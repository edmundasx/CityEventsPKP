<?php
declare(strict_types=1);

namespace App\Support;

/**
 * Lithuanian counties (apskritys), major cities, and common place names for location autocomplete.
 */
final class LithuaniaPlaces
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            "Alytaus apskritis",
            "Kauno apskritis",
            "Klaipėdos apskritis",
            "Marijampolės apskritis",
            "Panevėžio apskritis",
            "Šiaulių apskritis",
            "Tauragės apskritis",
            "Telšių apskritis",
            "Utenos apskritis",
            "Vilniaus apskritis",
            "Aukštaitija",
            "Dzūkija",
            "Mažoji Lietuva",
            "Sūduva",
            "Žemaitija",
            "Alytus",
            "Anykščiai",
            "Ariogala",
            "Birštonas",
            "Biržai",
            "Druskininkai",
            "Elektrėnai",
            "Gargždai",
            "Garliava",
            "Grigiškės",
            "Ignalina",
            "Jonava",
            "Joniškis",
            "Jurbarkas",
            "Kaišiadorys",
            "Kalvarija",
            "Kaunas",
            "Kazlų Rūda",
            "Kėdainiai",
            "Kelmė",
            "Klaipėda",
            "Kretinga",
            "Kuršėnai",
            "Lazdijai",
            "Lentvaris",
            "Marijampolė",
            "Mažeikiai",
            "Molėtai",
            "Neringa",
            "Naujoji Akmenė",
            "Palanga",
            "Panevėžys",
            "Pasvalys",
            "Plungė",
            "Priekulė",
            "Prienai",
            "Radviliškis",
            "Raseiniai",
            "Rietavas",
            "Rokiškis",
            "Šakiai",
            "Šalčininkai",
            "Šeduva",
            "Šiauliai",
            "Šilalė",
            "Šilutė",
            "Širvintos",
            "Skuodas",
            "Švenčionys",
            "Šventoji",
            "Tauragė",
            "Telšiai",
            "Trakai",
            "Ukmergė",
            "Utena",
            "Varėna",
            "Vievis",
            "Vilkaviškis",
            "Vilnius",
            "Visaginas",
            "Zarasai",
        ];
    }

    /**
     * Map center + zoom for each place name (approximate). Used by the home hero map.
     *
     * @return list<array{name: string, lat: float, lng: float, zoom: int}>
     */
    public static function mapTargets(): array
    {
        $coords = self::coordinateTable();
        $out = [];
        foreach (self::all() as $name) {
            $c = $coords[$name] ?? [55.17, 23.9, 8];
            $out[] = [
                "name" => $name,
                "lat" => $c[0],
                "lng" => $c[1],
                "zoom" => $c[2],
            ];
        }

        return $out;
    }

    /**
     * @return array<string, array{float, float, int}>
     */
    private static function coordinateTable(): array
    {
        return [
            "Alytaus apskritis" => [54.4, 24.05, 9],
            "Kauno apskritis" => [55.0, 23.85, 9],
            "Klaipėdos apskritis" => [55.7, 21.4, 9],
            "Marijampolės apskritis" => [54.55, 23.35, 9],
            "Panevėžio apskritis" => [54.4, 25.0, 9],
            "Šiaulių apskritis" => [55.9, 23.3, 9],
            "Tauragės apskritis" => [55.2, 22.3, 9],
            "Telšių apskritis" => [55.9, 22.5, 9],
            "Utenos apskritis" => [55.4, 25.6, 9],
            "Vilniaus apskritis" => [54.4, 25.0, 9],
            "Aukštaitija" => [55.7, 24.5, 8],
            "Dzūkija" => [54.2, 24.1, 8],
            "Mažoji Lietuva" => [54.5, 23.4, 8],
            "Sūduva" => [54.5, 23.4, 8],
            "Žemaitija" => [55.7, 22.0, 8],
            "Alytus" => [54.3963, 24.046, 11],
            "Anykščiai" => [55.5258, 25.1026, 11],
            "Ariogala" => [55.2646, 23.4777, 11],
            "Birštonas" => [54.6165, 24.0337, 11],
            "Biržai" => [56.2004, 24.7508, 11],
            "Druskininkai" => [54.0157, 23.9856, 11],
            "Elektrėnai" => [54.7854, 24.6366, 11],
            "Gargždai" => [55.7095, 21.3944, 11],
            "Garliava" => [54.8213, 23.8718, 11],
            "Grigiškės" => [54.6833, 25.0667, 11],
            "Ignalina" => [55.34, 26.1603, 11],
            "Jonava" => [55.0728, 24.2795, 11],
            "Joniškis" => [56.2333, 23.6167, 11],
            "Jurbarkas" => [55.0833, 22.7667, 11],
            "Kaišiadorys" => [54.8667, 24.45, 11],
            "Kalvarija" => [54.4167, 23.2333, 11],
            "Kaunas" => [54.8985, 23.9036, 11],
            "Kazlų Rūda" => [54.75, 23.5, 11],
            "Kėdainiai" => [55.2833, 23.9667, 11],
            "Kelmė" => [55.6333, 22.9333, 11],
            "Klaipėda" => [55.7068, 21.139, 11],
            "Kretinga" => [55.8888, 21.245, 11],
            "Kuršėnai" => [56.0, 22.9333, 11],
            "Lazdijai" => [54.2333, 23.5167, 11],
            "Lentvaris" => [54.6333, 25.05, 11],
            "Marijampolė" => [54.5555, 23.3545, 11],
            "Mažeikiai" => [56.3167, 22.3333, 11],
            "Molėtai" => [55.2333, 25.4167, 11],
            "Neringa" => [55.5, 21.1, 11],
            "Naujoji Akmenė" => [56.3167, 22.8833, 11],
            "Palanga" => [55.9175, 21.0686, 11],
            "Panevėžys" => [55.7372, 24.3505, 11],
            "Pasvalys" => [56.0667, 24.4, 11],
            "Plungė" => [55.91, 21.8446, 11],
            "Priekulė" => [55.5555, 21.3192, 11],
            "Prienai" => [54.6333, 24.0333, 11],
            "Radviliškis" => [55.8167, 23.5333, 11],
            "Raseiniai" => [55.3833, 23.1167, 11],
            "Rietavas" => [55.7167, 21.9333, 11],
            "Rokiškis" => [55.9667, 25.5833, 11],
            "Šakiai" => [54.95, 23.05, 11],
            "Šalčininkai" => [54.3, 25.3833, 11],
            "Šeduva" => [55.75, 23.7667, 11],
            "Šiauliai" => [55.9349, 23.3545, 11],
            "Šilalė" => [55.4833, 22.4833, 11],
            "Šilutė" => [55.35, 21.4833, 11],
            "Širvintos" => [54.95, 24.9333, 11],
            "Skuodas" => [56.2667, 21.5333, 11],
            "Švenčionys" => [55.0, 26.1667, 11],
            "Šventoji" => [55.9833, 21.0833, 11],
            "Tauragė" => [55.25, 22.2833, 11],
            "Telšiai" => [55.9833, 22.25, 11],
            "Trakai" => [54.6333, 24.9333, 11],
            "Ukmergė" => [55.25, 24.75, 11],
            "Utena" => [55.4833, 25.6, 11],
            "Varėna" => [54.2167, 24.4333, 11],
            "Vievis" => [54.7667, 24.8167, 11],
            "Vilkaviškis" => [54.65, 23.0333, 11],
            "Vilnius" => [54.6872, 25.2797, 11],
            "Visaginas" => [55.6, 26.4333, 11],
            "Zarasai" => [55.7333, 26.25, 11],
        ];
    }
}
