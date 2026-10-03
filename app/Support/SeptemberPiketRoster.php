<?php

namespace App\Support;

class SeptemberPiketRoster
{
    /**
     * PIKET BULAN SEPTEMBER nuw20260828_10270412.pdf, pages 1-3.
     * Explicit dates, not a weekly recurrence.
     * Each shift has three staff followed by its active coordinator.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function groups(): array
    {
        return [
            ['dates' => [1, 15, 29],
                'pagi' => ['Sulistyowati', 'Wiwik Yuniarsih', 'Sri Kusumastuti', 'Lilik Suratmi'],
                'siang' => ['Kasmi', 'Siti Munawaroh', 'Niken Dewi Hastika', 'Widodo'],
                'waka' => 'Niken Hari Pratiwi'],
            ['dates' => [2, 16, 30],
                'pagi' => ['Tutut Sriatin', 'Rika Okta Maulida', 'Mufatiroh', 'Elyana Frisca Monica'],
                'siang' => ['Siswanti Purwaningsih', 'Shinta Indyar Shanty Susanto', 'Dhuana Putri Puspitasary', 'Erwan Septiono'],
                'waka' => 'Hardini Indahing Budi'],
            ['dates' => [3, 17],
                'pagi' => ['Yuni Jiastuti', 'Yuli Ratnasari', 'Agus Pramono', 'Danang Anjar Hynwanto'],
                'siang' => ['Risqi Nur Imama', 'Luluk Munfarida', 'Tuhu Eries Kudori', 'Istiana Suhartati'],
                'waka' => 'Hendro Suwigyo'],
            ['dates' => [4, 18],
                'pagi' => ['Arif Setyobudi', 'Sunarti', 'Isti Mufadah', 'Joko Priyanto'],
                'siang' => ['Dra. Hanik Pangestuti', 'Andri Krisdianto', 'Fitria Renyasari', 'Agung Yulianto'],
                'waka' => 'Fajar Luthfianto'],
            ['dates' => [7, 21],
                'pagi' => ['Septiani', 'Martiin', 'Winarsih', 'Titin Sukmasari'],
                'siang' => ['Nurul Azizah', "Rifkotin Na'imah", 'Dra. Susakti Yuharini', 'Lutfia Marsalina'],
                'waka' => 'Setiyo Winarko'],
            ['dates' => [8, 22],
                'pagi' => ['Rindang Rejeki', 'Diana Hartanti', 'Veronica Damay Rulitasari', 'Ayu Puspitorini'],
                'siang' => ['Umi Kulsum', 'Ruly Dwi Setyaningrum', 'Sri Rahayu', 'Agustina Mardika Rini'],
                'waka' => 'Niken Hari Pratiwi'],
            ['dates' => [9, 23],
                'pagi' => ['Dra. Anik Indriani', 'Retno Widyastuti', 'Nur Eko Wahyuningsih', "Sa'ad Wazis Hiedayat"],
                'siang' => ['Purwati', 'Ratih Dian Irawati', 'Siti Maisaroh', 'Dyah Esti Rahayu'],
                'waka' => 'Hardini Indahing Budi'],
            ['dates' => [10, 24],
                'pagi' => ['Siti Umiharsih', 'Erna Rinawati', 'Astra Bella Flamboyan', 'Endang Ary Handayani'],
                'siang' => ['Ninik Sriwidayati', 'Endik Kuswantoro', 'Komariyah', 'Dian Mawarti'],
                'waka' => 'Hendro Suwigyo'],
            ['dates' => [11, 25],
                'pagi' => ['Yani', 'Titik Samsistini', 'Pipit Ambarwati', 'Kurnila Putri Islamawati'],
                'siang' => ['Basuki Sarjono', 'Atih Wilupi', 'Nishfu Laili', 'Nur Nastutisari'],
                'waka' => 'Fajar Luthfianto'],
            ['dates' => [14, 28],
                'pagi' => ['Siti Khoiriyah', 'Peni Wulandari', 'Badrus Sulaiman', 'Dwi Rini Manfaati'],
                'siang' => ["Elysa Yuli Nur'aini", 'Dwi Nova Setyandari', "Mas'an Widodo", 'Dwi Kuswanto'],
                'waka' => 'Setiyo Winarko'],
        ];
    }

    public static function canonicalName(string $name): string
    {
        // Spelling differences confirmed by the owner; never use fuzzy matching.
        return match ($name) {
            'Erwan Septiono' => 'Erwan Septiyono',
            'Danang Anjar Hynwanto' => 'Danang Anjar Hymawanto',
            'Fitria Renyasari' => 'Fitria Renytasari',
            'Lutfa Marsalina' => 'Lutfia Marsalina',
            "Sa'ad Wazis Hidayat" => "Sa'ad Wazis Hiedayat",
            default => $name,
        };
    }

    public static function nameKey(string $name): string
    {
        $name = explode(',', $name)[0];
        $name = preg_replace('/\s+(?:S\.Pd|S\.Kom|S\.Psi|S\.E|S\.Si|S\.Sn|S\.Ag|S\.T|S\.Ds|S\.Tr|S\.ST|SS|ST)\b.*$/i', '', $name);

        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }
}
