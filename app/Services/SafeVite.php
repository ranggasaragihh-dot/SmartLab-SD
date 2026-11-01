<?php

namespace App\Services;

use Illuminate\Foundation\Vite;

/**
 * Vite yang tidak bikin tampilan rusak.
 *
 * Masalah bawaan: selama file public/hot ada, Laravel selalu mengarahkan CSS/JS
 * ke dev server Vite. Halaman jadi tanpa CSS kalau:
 *  - alamat di public/hot adalah 0.0.0.0 (tidak bisa dibuka browser / HP),
 *  - `npm run dev` sudah mati tapi public/hot tertinggal,
 *  - website dibuka lewat tunnel (trycloudflare) yang tidak meneruskan port 5173.
 *
 * Solusi: pakai dev server hanya jika benar-benar bisa dijangkau, dan arahkan ke
 * host yang sama dengan yang dipakai browser. Selain itu pakai public/build.
 */
class SafeVite extends Vite
{
    protected ?bool $hotUsable = null;

    public function isRunningHot()
    {
        return $this->hotUsable ??= parent::isRunningHot()
            && $this->requestCanReachDevServer()
            && $this->devServerIsAlive();
    }

    protected function hotAsset($asset)
    {
        $url = parse_url(rtrim(file_get_contents($this->hotFile())));

        $scheme = $url['scheme'] ?? 'http';
        $port = isset($url['port']) ? ':'.$url['port'] : '';

        // Ganti host di file hot (mis. 0.0.0.0) dengan host yang dibuka browser,
        // supaya laptop (127.0.0.1) maupun HP (IP LAN) sama-sama bisa memuat aset.
        return $scheme.'://'.request()->getHost().$port.'/'.$asset;
    }

    /**
     * Dev server hanya bisa dijangkau dari localhost atau jaringan lokal (LAN).
     */
    protected function requestCanReachDevServer(): bool
    {
        $request = request();

        if ($request->isSecure()) {
            return false;
        }

        $host = $request->getHost();

        if ($host === 'localhost') {
            return true;
        }

        return filter_var($host, FILTER_VALIDATE_IP) !== false
            && filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /**
     * Pastikan `npm run dev` masih hidup (file hot bisa tertinggal setelah dimatikan).
     */
    protected function devServerIsAlive(): bool
    {
        $port = parse_url(rtrim(file_get_contents($this->hotFile())), PHP_URL_PORT) ?? 5173;

        $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.2);

        if ($socket === false) {
            return false;
        }

        fclose($socket);

        return true;
    }
}
