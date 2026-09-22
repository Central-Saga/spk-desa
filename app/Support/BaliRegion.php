<?php

namespace App\Support;

final class BaliRegion
{
    /**
     * Kabupaten/kota => [kecamatan => kode pos representatif].
     * Sumber: data Kemendagri & kode pos Pos Indonesia (per kecamatan, satu kode
     * representatif; desa dalam satu kecamatan bisa bervariasi, user dapat edit manual).
     *
     * @var array<string, array<string, string>>
     */
    public const REGIONS = [
        'Badung' => [
            'Kuta' => '80361', 'Kuta Utara' => '80361', 'Kuta Selatan' => '80361',
            'Mengwi' => '80351', 'Abiansemal' => '80352', 'Petang' => '80353',
        ],
        'Bangli' => [
            'Bangli' => '80611', 'Susut' => '80661', 'Tembuku' => '80671', 'Kintamani' => '80652',
        ],
        'Buleleng' => [
            'Buleleng' => '81119', 'Busungbiu' => '81154', 'Gerokgak' => '81155',
            'Kubutambahan' => '81172', 'Sawan' => '81171', 'Seririt' => '81152',
            'Banjar' => '81152', 'Sukasada' => '81161', 'Tejakula' => '81173',
        ],
        'Gianyar' => [
            'Gianyar' => '80511', 'Blahbatuh' => '80581', 'Payangan' => '80572',
            'Tegallalang' => '80561', 'Tampaksiring' => '80552', 'Ubud' => '80571',
            'Sukawati' => '80582',
        ],
        'Jembrana' => [
            'Negara' => '82211', 'Mendoyo' => '82261', 'Melaya' => '82252',
            'Pekutatan' => '82262', 'Jembrana' => '82218',
        ],
        'Karangasem' => [
            'Karangasem' => '80811', 'Abang' => '80852', 'Bebandem' => '80861',
            'Kubu' => '80853', 'Manggis' => '80871', 'Rendang' => '80863',
            'Selat' => '80862', 'Sidemen' => '80864',
        ],
        'Klungkung' => [
            'Klungkung' => '80716', 'Banjarangkan' => '80752', 'Dawan' => '80761',
            'Nusa Penida' => '80771',
        ],
        'Tabanan' => [
            'Tabanan' => '82111', 'Baturiti' => '82191', 'Kerambitan' => '82161',
            'Kediri' => '82121', 'Marga' => '82181', 'Penebel' => '82152',
            'Pupuan' => '82163', 'Selemadeg' => '82162', 'Selemadeg Barat' => '82163',
            'Selemadeg Timur' => '82161',
        ],
        'Kota Denpasar' => [
            'Denpasar Barat' => '80111', 'Denpasar Selatan' => '80228',
            'Denpasar Timur' => '80234', 'Denpasar Utara' => '80116',
        ],
    ];

    /**
     * @return array<string, list<string>> kabupaten => daftar kecamatan
     */
    public static function kecamatanByKabupaten(): array
    {
        return array_map(array_keys(...), self::REGIONS);
    }

    /**
     * @return list<string> daftar nama kabupaten/kota
     */
    public static function kabupaten(): array
    {
        return array_keys(self::REGIONS);
    }

    /**
     * Kode pos representatif kecamatan, null jika tidak dikenal.
     */
    public static function kodePos(string $kabupaten, string $kecamatan): ?string
    {
        return self::REGIONS[$kabupaten][$kecamatan] ?? null;
    }
}
