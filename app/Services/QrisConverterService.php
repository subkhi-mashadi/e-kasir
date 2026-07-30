<?php

namespace App\Services;

class QrisConverterService
{
    /**
     * Convert a static QRIS string to dynamic with embedded amount and reference.
     */
    public function toDynamic(string $staticQris, int $amount, string $referenceId): string
    {
        $qris = substr(trim($staticQris), 0, -8);

        $qris = str_replace('010211', '010212', $qris);

        $qris = preg_replace('/54\d{2}\d+/', '', $qris);

        $amountStr = (string) $amount;
        $tag54     = '54' . str_pad(strlen($amountStr), 2, '0', STR_PAD_LEFT) . $amountStr;
        if (str_contains($qris, '5303360')) {
            $qris = str_replace('5303360', '5303360' . $tag54, $qris);
        } else {
            $pos  = strpos($qris, '5802');
            $qris = $pos !== false
                ? substr($qris, 0, $pos) . $tag54 . substr($qris, $pos)
                : $qris . $tag54;
        }

        $ref     = substr($referenceId, 0, 25);
        $subTag  = '05' . str_pad(strlen($ref), 2, '0', STR_PAD_LEFT) . $ref;
        $tag62   = '62' . str_pad(strlen($subTag), 2, '0', STR_PAD_LEFT) . $subTag;
        $qris   .= $tag62;

        $forCrc = $qris . '6304';
        $crc    = $this->crc16($forCrc);

        return $forCrc . strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    private function crc16(string $data): int
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= ord($data[$i]) << 8;
            for ($j = 0; $j < 8; $j++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }
        return $crc;
    }
}
