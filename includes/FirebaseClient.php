<?php
/**
 * Klien Firebase Realtime Database sederhana berbasis REST + cURL.
 * Dipakai murni di sisi server (PHP native) untuk menulis data secara aman
 * memakai Database Secret. Pembacaan realtime di browser tetap memakai
 * Firebase JS SDK langsung (lihat assets/js/*.js).
 */
class FirebaseClient
{
    /** @var string */
    private $baseUrl;
    /** @var string */
    private $secret;

    public function __construct($databaseUrl, $secret)
    {
        $this->baseUrl = rtrim($databaseUrl, '/');
        $this->secret  = $secret;
    }

    /** GET data pada path tertentu (tanpa trailing .json) */
    public function get($path)
    {
        $res = $this->request('GET', $path);
        return is_array($res) ? $res : [];
    }

    /** PUT (overwrite) data pada path tertentu */
    public function put($path, array $data)
    {
        return $this->request('PUT', $path, $data);
    }

    /** PATCH (update sebagian) data pada path tertentu */
    public function patch($path, array $data)
    {
        return $this->request('PATCH', $path, $data);
    }

    /** POST (push, membuat key unik) data pada path tertentu */
    public function push($path, array $data)
    {
        return $this->request('POST', $path, $data);
    }

    /** DELETE (menghapus) data pada path tertentu */
    public function delete($path)
    {
        return $this->request('DELETE', $path);
    }

    private function request($method, $path, array $data = null)
    {
        $path = ltrim($path, '/');
        $url  = $this->baseUrl . '/' . $path . '.json?auth=' . urlencode($this->secret);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $errNo    = curl_errno($ch);
        $err      = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errNo) {
            throw new RuntimeException('Koneksi ke Firebase gagal: ' . $err);
        }
        if ($httpCode >= 400) {
            throw new RuntimeException('Firebase menolak permintaan (HTTP ' . $httpCode . '): ' . $response);
        }

        $decoded = json_decode($response, true);
        return $decoded ?? [];
    }
}
