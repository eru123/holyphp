<?php

declare(strict_types=1);

namespace eru123\email\provider;

use Exception;

class SMTP implements ProviderInterface
{
    private $config;

    public function __construct(array $config)
    {
        $this->buildConfig($config);
        if ($this->config['secure'] && !in_array($this->config['secure'], ['ssl', 'tls'])) {
            throw new Exception('Invalid secure type: ' . $this->config['secure']);
        }
    }

    private function buildConfig(array $config = []): array
    {
        $cfg = array_merge([
            'host' => 'localhost',
            'port' => 587,
            'timeout' => 30,
            'auth' => true,
            'username' => '',
            'password' => '',
            'secure' => false,
            'debug' => false,
            'ssl' => false,
            'time' => time(),
        ], $this->config ?? [], $config);

        if ($cfg['secure'] == 'ssl') {
            $cfg['ssl'] = array_merge([
                'verify_peer' => false,
                'verify_depth' => 3,
                'allow_self_signed' => true,
                'peer_name' => $cfg['host'],
                'cafile' => realpath(__DIR__ . '/../cert/cacert.pem'),
            ], (is_array($cfg['ssl']) ? $cfg['ssl'] : []));
        }

        if ($cfg['ssl'] && !isset($cfg['ssl']['peer_name'])) {
            $cfg['ssl']['peer_name'] = $cfg['host'];
        }

        $this->config = $cfg;
        return $cfg;
    }

    private function debug($message): void
    {
        if (!$this->config['debug']) {
            return;
        }

        if (in_array(PHP_SAPI, ['cli', 'phpdbg'])) {
            echo "[", date('Y-m-d H:i:s'), "] ", $message, PHP_EOL;
        } else {
            echo '<pre>', $message, '</pre>';
        }
    }

    public function connect(array $config = [], bool $debug = false)
    {
        $cfg = $this->buildConfig($config);
        !$debug || $this->debug('Connecting to ' . $cfg['host'] . ':' . $cfg['port']);
        if ($cfg['ssl']) {
            !$debug || $this->debug('Using SSL');
            if (!extension_loaded('openssl')) {
                throw new Exception('SSL extension not loaded');
            }

            $context = stream_context_create([
                'ssl' => $cfg['ssl']
            ]);

            $socket = stream_socket_client('ssl://' . $cfg['host'] . ':' . $cfg['port'], $errno, $errstr, $cfg['timeout'], STREAM_CLIENT_CONNECT, $context);
        } else {
            $socket = fsockopen($cfg['host'], $cfg['port'], $errno, $errstr, $cfg['timeout']);
        }

        if (!$socket) {
            throw new Exception($errstr, $errno);
        }

        return $socket;
    }

    public function send(array $data): bool
    {
        try {
            $data = array_merge([
                'from_email' => '',
                'from_name' => '',
                'reply_to' => false,
                'to' => [],
                'cc' => [],
                'bcc' => [],
                'subject' => '',
                'body' => '',
                'attachments' => [],
            ], $data);

            $socket = $this->connect([], $this->config['debug']);

            $this->debug('Checking connection');
            $this->debug('RECV ' . $this->read($socket));

            $this->debug('Connected, sending HELO');
            $this->write($socket, 'EHLO ' . $this->config['host']);
            $this->debug('RECV ' . $this->read($socket));

            if ($this->config['secure'] == 'tls') {
                $this->debug('Starting TLS');
                $this->write($socket, 'STARTTLS');
                $this->debug('RECV ' . $this->read($socket));

                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new Exception('Unable to start TLS');
                }

                $this->debug('Sending HELO after TLS');
                $this->write($socket, 'EHLO ' . $this->config['host']);
                $this->debug('RECV ' . $this->read($socket));
            }

            if ($this->config['auth']) {
                $this->write($socket, 'AUTH LOGIN');
                $this->debug('RECV ' . $this->read($socket));

                $this->write($socket, base64_encode($this->config['username']));
                $this->debug('RECV ' . $this->read($socket));

                $this->write($socket, base64_encode($this->config['password']));
                $this->debug('RECV ' . $this->read($socket));
            }

            $this->write($socket, 'MAIL FROM: <' . $data['from_email'] . '>');
            $this->debug('RECV ' . $this->read($socket));

            foreach ($data['to'] as $to) {
                $this->write($socket, 'RCPT TO: <' . $to . '>');
                $this->debug('RECV ' . $this->read($socket));
            }

            foreach ($data['cc'] as $cc) {
                $this->write($socket, 'RCPT TO: <' . $cc . '>');
                $this->debug('RECV ' . $this->read($socket));
            }

            foreach ($data['bcc'] as $bcc) {
                $this->write($socket, 'RCPT TO: <' . $bcc . '>');
                $this->debug('RECV ' . $this->read($socket));
            }

            $this->write($socket, 'DATA');
            $this->debug('RECV ' . $this->read($socket));

            $this->write($socket, 'From: ' . $data['from_name'] . ' <' . $data['from_email'] . '>');
            $this->write($socket, 'To: ' . implode(', ', $data['to']));

            if ($data['reply_to']) {
                $this->write($socket, 'Reply-To: ' . (is_array($data['reply_to']) ? implode(', ', $data['reply_to']) : $data['reply_to']));
            }

            if (!empty($data['cc'])) {
                $this->write($socket, 'Cc: ' . implode(', ', $data['cc']));
            }
            if (!empty($data['bcc'])) {
                $this->write($socket, 'Bcc: ' . implode(', ', $data['bcc']));
            }

            $hash = md5((string) $this->config['time']);

            $this->write($socket, 'Subject: ' . $data['subject']);
            $this->write($socket, 'MIME-Version: 1.0');
            $this->write($socket, 'Content-Type: multipart/mixed; boundary="=_NextPart_' . $hash . '"');
            $this->write($socket, '');
            $this->write($socket, '--=_NextPart_' . $hash);
            $this->write($socket, 'Content-Type: text/html; charset="utf-8"');
            $this->write($socket, 'Content-Transfer-Encoding: 8bit');
            $this->write($socket, '');
            $this->write($socket, $data['body']);
            $this->write($socket, '');

            foreach ($data['attachments'] as $attachment) {
                $this->write($socket, '--=_NextPart_' . $hash);
                $this->write($socket, 'Content-Type: ' . $attachment['type'] . '; name="' . $attachment['name'] . '"');
                $this->write($socket, 'Content-Transfer-Encoding: base64');
                $this->write($socket, 'Content-Disposition: attachment; filename="' . $attachment['name'] . '"');
                $this->write($socket, '');
                $this->write($socket, chunk_split(base64_encode($attachment['content'])));
                $this->write($socket, '');
            }

            $this->write($socket, '--=_NextPart_' . $hash . '--');
            $this->write($socket, '.');

            $datr = $this->read($socket);
            $this->debug('RECV ' . $datr);

            $this->write($socket, 'QUIT');
            $this->debug('RECV ' . $this->read($socket));

            fclose($socket);
            return true;
        } catch (Exception $e) {
            fclose($socket);
            return false;
        }
    }

    private function write($socket, $data): void
    {
        $this->debug("SEND " . substr($data, 0, 64) . (strlen($data) > 64 ? '...' : ''));
        fwrite($socket, $data . "\r\n");
    }

    private function read($socket): string
    {
        $data = '';
        while ($str = fgets($socket, 512)) {
            $data .= $str;
            if (substr($str, 0, 1) == '4' || substr($str, 0, 1) == '5') {
                throw new Exception($str);
            }
            if (substr($str, 3, 1) == ' ') {
                break;
            }
        }
        return $data;
    }

    public function __destruct()
    {
        $this->config = null;
    }
}
