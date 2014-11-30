<?php

namespace Jigoshop\Helper;

use Jigoshop\Core\Options;

/**
 * Country helper.
 *
 * @package Jigoshop\Helper
 */
class Country
{
	protected static $countries = array(
		'AD' => 'Andorra',
		'AE' => 'United Arab Emirates',
		'AF' => 'Afghanistan',
		'AG' => 'Antigua and Barbuda',
		'AI' => 'Anguilla',
		'AL' => 'Albania',
		'AM' => 'Armenia',
		'AN' => 'Netherlands Antilles',
		'AO' => 'Angola',
		'AQ' => 'Antarctica',
		'AR' => 'Argentina',
		'AS' => 'American Samoa',
		'AT' => 'Austria',
		'AU' => 'Australia',
		'AW' => 'Aruba',
		'AX' => 'Aland Islands',
		'AZ' => 'Azerbaijan',
		'BA' => 'Bosnia and Herzegovina',
		'BB' => 'Barbados',
		'BD' => 'Bangladesh',
		'BE' => 'Belgium',
		'BF' => 'Burkina Faso',
		'BG' => 'Bulgaria',
		'BH' => 'Bahrain',
		'BI' => 'Burundi',
		'BJ' => 'Benin',
		'BL' => 'Saint Barthélemy',
		'BM' => 'Bermuda',
		'BN' => 'Brunei',
		'BO' => 'Bolivia',
		'BR' => 'Brazil',
		'BS' => 'Bahamas',
		'BT' => 'Bhutan',
		'BW' => 'Botswana',
		'BY' => 'Belarus',
		'BZ' => 'Belize',
		'CA' => 'Canada',
		'CC' => 'Cocos (Keeling) Islands',
		'CD' => 'Congo (Kinshasa)',
		'CF' => 'Central African Republic',
		'CG' => 'Congo (Brazzaville)',
		'CH' => 'Switzerland',
		'CI' => 'Ivory Coast',
		'CK' => 'Cook Islands',
		'CL' => 'Chile',
		'CM' => 'Cameroon',
		'CN' => 'China',
		'CO' => 'Colombia',
		'CR' => 'Costa Rica',
		'CU' => 'Cuba',
		'CV' => 'Cape Verde',
		'CX' => 'Christmas Island',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DE' => 'Germany',
		'DJ' => 'Djibouti',
		'DK' => 'Denmark',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'DZ' => 'Algeria',
		'EC' => 'Ecuador',
		'EE' => 'Estonia',
		'EG' => 'Egypt',
		'EH' => 'Western Sahara',
		'ER' => 'Eritrea',
		'ES' => 'Spain',
		'ET' => 'Ethiopia',
		'FI' => 'Finland',
		'FJ' => 'Fiji',
		'FK' => 'Falkland Islands',
		'FM' => 'Micronesia',
		'FO' => 'Faroe Islands',
		'FR' => 'France',
		'GA' => 'Gabon',
		'GB' => 'United Kingdom',
		'GD' => 'Grenada',
		'GE' => 'Georgia',
		'GF' => 'French Guiana',
		'GG' => 'Guernsey',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GL' => 'Greenland',
		'GM' => 'Gambia',
		'GN' => 'Guinea',
		'GP' => 'Guadeloupe',
		'GQ' => 'Equatorial Guinea',
		'GR' => 'Greece',
		'GS' => 'South Georgia/Sandwich Islands',
		'GT' => 'Guatemala',
		'GU' => 'Guam',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HK' => 'Hong Kong',
		'HN' => 'Honduras',
		'HR' => 'Croatia',
		'HT' => 'Haiti',
		'HU' => 'Hungary',
		'ID' => 'Indonesia',
		'IE' => 'Ireland',
		'IL' => 'Israel',
		'IM' => 'Isle of Man',
		'IN' => 'India',
		'IO' => 'British Indian Ocean Territory',
		'IQ' => 'Iraq',
		'IR' => 'Iran',
		'IS' => 'Iceland',
		'IT' => 'Italy',
		'JE' => 'Jersey',
		'JM' => 'Jamaica',
		'JO' => 'Jordan',
		'JP' => 'Japan',
		'KE' => 'Kenya',
		'KG' => 'Kyrgyzstan',
		'KH' => 'Cambodia',
		'KI' => 'Kiribati',
		'KM' => 'Comoros',
		'KN' => 'Saint Kitts and Nevis',
		'KP' => 'North Korea',
		'KR' => 'South Korea',
		'KW' => 'Kuwait',
		'KY' => 'Cayman Islands',
		'KZ' => 'Kazakhstan',
		'LA' => 'Laos',
		'LB' => 'Lebanon',
		'LC' => 'Saint Lucia',
		'LI' => 'Liechtenstein',
		'LK' => 'Sri Lanka',
		'LR' => 'Liberia',
		'LS' => 'Lesotho',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'LV' => 'Latvia',
		'LY' => 'Libya',
		'MA' => 'Morocco',
		'MC' => 'Monaco',
		'MD' => 'Moldova',
		'ME' => 'Montenegro',
		'MF' => 'Saint Martin (French part)',
		'MG' => 'Madagascar',
		'MH' => 'Marshall Islands',
		'MK' => 'Macedonia',
		'ML' => 'Mali',
		'MM' => 'Myanmar',
		'MN' => 'Mongolia',
		'MO' => 'Macao S.A.R., China',
		'MP' => 'Northern Mariana Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MS' => 'Montserrat',
		'MT' => 'Malta',
		'MU' => 'Mauritius',
		'MV' => 'Maldives',
		'MW' => 'Malawi',
		'MX' => 'Mexico',
		'MY' => 'Malaysia',
		'MZ' => 'Mozambique',
		'NA' => 'Namibia',
		'NC' => 'New Caledonia',
		'NE' => 'Niger',
		'NF' => 'Norfolk Island',
		'NG' => 'Nigeria',
		'NI' => 'Nicaragua',
		'NL' => 'Netherlands',
		'NO' => 'Norway',
		'NP' => 'Nepal',
		'NR' => 'Nauru',
		'NU' => 'Niue',
		'NZ' => 'New Zealand',
		'OM' => 'Oman',
		'PA' => 'Panama',
		'PE' => 'Peru',
		'PF' => 'French Polynesia',
		'PG' => 'Papua New Guinea',
		'PH' => 'Philippines',
		'PK' => 'Pakistan',
		'PL' => 'Poland',
		'PM' => 'Saint Pierre and Miquelon',
		'PN' => 'Pitcairn',
		'PR' => 'Puerto Rico',
		'PS' => 'Palestinian Territory',
		'PT' => 'Portugal',
		'PW' => 'Palau',
		'PY' => 'Paraguay',
		'QA' => 'Qatar',
		'RE' => 'Reunion',
		'RO' => 'Romania',
		'RS' => 'Serbia',
		'RU' => 'Russia',
		'RW' => 'Rwanda',
		'SA' => 'Saudi Arabia',
		'SB' => 'Solomon Islands',
		'SC' => 'Seychelles',
		'SD' => 'Sudan',
		'SE' => 'Sweden',
		'SG' => 'Singapore',
		'SH' => 'Saint Helena',
		'SI' => 'Slovenia',
		'SJ' => 'Svalbard and Jan Mayen',
		'SK' => 'Slovakia',
		'SL' => 'Sierra Leone',
		'SM' => 'San Marino',
		'SN' => 'Senegal',
		'SO' => 'Somalia',
		'SR' => 'Suriname',
		'ST' => 'Sao Tome and Principe',
		'SV' => 'El Salvador',
		'SY' => 'Syria',
		'SZ' => 'Swaziland',
		'TC' => 'Turks and Caicos Islands',
		'TD' => 'Chad',
		'TF' => 'French Southern Territories',
		'TG' => 'Togo',
		'TH' => 'Thailand',
		'TJ' => 'Tajikistan',
		'TK' => 'Tokelau',
		'TL' => 'Timor-Leste',
		'TM' => 'Turkmenistan',
		'TN' => 'Tunisia',
		'TO' => 'Tonga',
		'TR' => 'Turkey',
		'TT' => 'Trinidad and Tobago',
		'TV' => 'Tuvalu',
		'TW' => 'Taiwan',
		'TZ' => 'Tanzania',
		'UA' => 'Ukraine',
		'UG' => 'Uganda',
		'UM' => 'US Minor Outlying Islands',
		'US' => 'United States',
		'USAF' => 'US Armed Forces',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VA' => 'Vatican',
		'VC' => 'Saint Vincent and the Grenadines',
		'VE' => 'Venezuela',
		'VG' => 'British Virgin Islands',
		'VI' => 'U.S. Virgin Islands',
		'VN' => 'Viet nam',
		'VU' => 'Vanuatu',
		'WF' => 'Wallis and Futuna',
		'WS' => 'Samoa',
		'YE' => 'Yemen',
		'YT' => 'Mayotte',
		'ZA' => 'South Africa',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe'
	);

	protected static $states = array(
		// Albania: Prefectures ("qarks")
		'AL' => array(
			'BER' => 'Berat',
			'DIB' => 'Dibër',
			'DUR' => 'Durrës',
			'ELB' => 'Elbasan',
			'FIE' => 'Fier',
			'GJI' => 'Gjirokastër',
			'KOR' => 'Korçë',
			'KUK' => 'Kukës',
			'LEZ' => 'Lezhë',
			'SHK' => 'Shkodër',
			'TIR' => 'Tiranë',
			'VLO' => 'Vlorë'
		),
		'AU' => array(
			'ACT' => 'Australian Capital Territory',
			'NSW' => 'New South Wales',
			'NT' => 'Northern Territory',
			'QLD' => 'Queensland',
			'SA' => 'South Australia',
			'TAS' => 'Tasmania',
			'VIC' => 'Victoria',
			'WA' => 'Western Australia'
		),
		'BR' => array(
			'AC' => 'Acre',
			'AL' => 'Alagoas',
			'AM' => 'Amazonas',
			'AP' => 'Amapá',
			'BA' => 'Bahia',
			'CE' => 'Ceará',
			'DF' => 'Distrito federal',
			'ES' => 'Espírito santo',
			'GO' => 'Goiás',
			'MA' => 'Maranhão',
			'MG' => 'Minas gerais',
			'MS' => 'Mato grosso do sul',
			'MT' => 'Mato grosso',
			'PA' => 'Pará',
			'PB' => 'Paraiba',
			'PE' => 'Pernambuco',
			'PI' => 'Piauí',
			'PR' => 'Paraná',
			'RJ' => 'Rio de janeiro',
			'RN' => 'Rio grande do norte',
			'RO' => 'Rondônia',
			'RR' => 'Roraima',
			'RS' => 'Rio grande do sul',
			'SC' => 'Santa catarina',
			'SE' => 'Sergipe',
			'SP' => 'São paulo',
			'TO' => 'Tocantins'
		),
		'CA' => array(
			'AB' => 'Alberta',
			'BC' => 'British Columbia',
			'MB' => 'Manitoba',
			'NB' => 'New Brunswick',
			'NL' => 'Newfoundland',
			'NS' => 'Nova Scotia',
			'NT' => 'Northwest Territories',
			'NU' => 'Nunavut',
			'ON' => 'Ontario',
			'PE' => 'Prince Edward Island',
			'QC' => 'Quebec',
			'SK' => 'Saskatchewan',
			'YT' => 'Yukon Territory'
		),
		// Switzerland: Cantons
		'CH' => array(
			'AG' => 'Aargau',
			'AI' => 'Appenzell Innerrhoden',
			'AR' => 'Appenzell Ausserrhoden',
			'BE' => 'Bern',
			'BL' => 'Basel-Landschaft',
			'BS' => 'Basel-Stadt',
			'FR' => 'Freiburg',
			'GE' => 'Genf',
			'GL' => 'Glarus',
			'GR' => 'Graubünden',
			'JU' => 'Jura',
			'LU' => 'Luzern',
			'NE' => 'Neuenburg',
			'NW' => 'Nidwalden',
			'OW' => 'Obwalden',
			'SG' => 'St. Gallen',
			'SH' => 'Schaffhausen',
			'SO' => 'Solothurn',
			'SZ' => 'Schwyz',
			'TG' => 'Thurgau',
			'TI' => 'Tessin',
			'UR' => 'Uri',
			'VD' => 'Waadt',
			'VS' => 'Wallis',
			'ZG' => 'Zug',
			'ZH' => 'Zürich'
		),
		// Spain: Provinces
		'ES' => array(
			'AA' => 'Álava',
			'AB' => 'Albacete',
			'AN' => 'Alicante',
			'AM' => 'Almería',
			'AS' => 'Asturias',
			'AV' => 'Ávila',
			'BD' => 'Badajoz',
			'BL' => 'Baleares',
			'BR' => 'Barcelona',
			'BU' => 'Burgos',
			'CC' => 'Cáceres',
			'CD' => 'Cádiz',
			'CN' => 'Cantabria',
			'CS' => 'Castellón',
			'CE' => 'Ceuta',
			'CR' => 'Ciudad Real',
			'CO' => 'Córdoba',
			'CU' => 'Cuenca',
			'GN' => 'Gerona',
			'GD' => 'Granada',
			'GJ' => 'Guadalajara',
			'GP' => 'Guipúzcoa',
			'HL' => 'Huelva',
			'HS' => 'Huesca',
			'JA' => 'Jaén',
			'AC' => 'La Coruña',
			'LR' => 'La Rioja',
			'LP' => 'Las Palmas',
			'LN' => 'León',
			'LD' => 'Lérida',
			'LG' => 'Lugo',
			'MD' => 'Madrid',
			'MG' => 'Málaga',
			'ME' => 'Melilla',
			'MR' => 'Murcia',
			'NV' => 'Navarra',
			'OR' => 'Orense',
			'PL' => 'Palencia',
			'PV' => 'Pontevedra',
			'SL' => 'Salamanca',
			'SC' => 'Santa Cruz de Tenerife',
			'SG' => 'Segovia',
			'SV' => 'Sevilla',
			'SR' => 'Soria',
			'TG' => 'Tarragona',
			'TE' => 'Teruel',
			'TD' => 'Toledo',
			'VN' => 'Valencia',
			'VD' => 'Valladolid',
			'VZ' => 'Vizcaya',
			'ZM' => 'Zamora',
			'ZG' => 'Zaragoza'
		),
		// Czech Republic: Regions
		'CZ' => array(
			'JC' => 'Jihoceský kraj [South Bohemian Region]',
			'JM' => 'Jihomoravský kraj [South Moravian Region]',
			'KA' => 'Karlovarský kraj [Karlovy Vary Region]',
			'KR' => 'Královéhradecký kraj [Hradec Králové Region]',
			'LI' => 'Liberecký kraj [Liberec Region]',
			'MO' => 'Moravskoslezský kraj [Moravian-Silesian Region]',
			'OL' => 'Olomoucký kraj [Olomouc Region]',
			'PA' => 'Pardubický kraj [Pardubice Region]',
			'PL' => 'Plzenský kraj [Plzen Region]',
			'PR' => 'Praha (Hlavni mesto Praha) [Prague]',
			'ST' => 'Stredoceský kraj [Central Bohemian Region]',
			'US' => 'Ústecký kraj [Ústí Region]',
			'VY' => 'Vysocina',
			'ZL' => 'Zlínský kraj [Zlín Region]'
		),
		// Germany: Federal States
		'DE' => array(
			'NDS' => 'Niedersachsen',
			'BAW' => 'Baden-Württemberg',
			'BAY' => 'Bayern',
			'BER' => 'Berlin',
			'BRG' => 'Brandenburg',
			'BRE' => 'Bremen',
			'HAM' => 'Hamburg',
			'HES' => 'Hessen',
			'MEC' => 'Mecklenburg-Vorpommern',
			'NRW' => 'Nordrhein-Westfalen',
			'RHE' => 'Rheinland-Pfalz',
			'SAR' => 'Saarland',
			'SAS' => 'Sachsen',
			'SAC' => 'Sachsen-Anhalt',
			'SCN' => 'Schleswig-Holstein',
			'THE' => 'Thüringen'
		),
		// Finland: Regions
		'FI' => array(
			'ÅAL' => 'Åland',
			'EKA' => 'Etelä-Karjala [South Karelia]',
			'EPO' => 'Etelä-Pohjanmaa [South Ostrobothnia]',
			'ESA' => 'Etelä-Savo',
			'KAI' => 'Kainuu',
			'KHA' => 'Kanta-Häme',
			'KPO' => 'Keski-Pohjanmaa [Central Ostrobothnia]',
			'KSO' => 'Keski-Suomi [Central Finland]',
			'KYM' => 'Kymenlaakso (Kymmenedalen)',
			'LAP' => 'Lappi [Lapland]',
			'PHA' => 'Päijät-Häme',
			'PIR' => 'Pirkanmaa',
			'POH' => 'Pohjanmaa [Ostrobothnia]',
			'PKA' => 'Pohjois-Karjala [North Karelia]',
			'PPO' => 'Pohjois-Pohjanmaa [North Ostrobothnia]',
			'PSA' => 'Pohjois-Savo',
			'SAT' => 'Satakunta',
			'UUS' => 'Uusimaa (Nyland)',
			'VSS' => 'Varsinais-Suomi (Egentliga Finland)'
		),
		// France: Regions
		'FR' => array(
			'ALS' => 'Alsace',
			'AQU' => 'Aquitaine',
			'AUV' => 'Auvergne',
			'BAS' => 'Basse-Normandie [Lower Normandy]',
			'BOU' => 'Bourgogne [Burgundy]',
			'BRE' => 'Bretagne [Brittany]',
			'CEN' => 'Centre',
			'CHA' => 'Champagne - Ardenne',
			'COR' => 'Corse',
			'FRA' => 'Franche-Comté',
			'HAU' => 'Haute-Normandie [Upper Normandy]',
			'ILE' => 'Île-de-France',
			'LAN' => 'Languedoc - Roussillon',
			'LIM' => 'Limousin',
			'LOR' => 'Lorraine',
			'MID' => 'Midi - Pyrénées',
			'NOR' => 'Nord - Pas-de-Calais',
			'PAY' => 'Pays de la Loire',
			'PIC' => 'Picardie',
			'POI' => 'Poitou - Charentes',
			'PRO' => 'Provence - Alpes - Côte d\'Azur',
			'RHO' => 'Rhône - Alpes'
		),
		// Greece: Regions
		'GR' => array(
			'AOR' => 'Ágio Óros [Mount Athos]',
			'AMT' => 'Anatolikí Makedonía & Thrakí [East Macedonia & Thrace]',
			'ATT' => 'Attikí [Attica]',
			'DEL' => 'Dytikí Elláda [Western Greece]',
			'DMD' => 'Dytikí Makedonía [West Macedonia]',
			'ION' => 'Iónia Nisiá [Ionian Islands]',
			'IPI' => 'Ípiros [Epirus]',
			'KMD' => 'Kedrikí Makedonía [Central Macedonia]',
			'KRI' => 'Kríti [Crete]',
			'NAI' => 'Nótio Aigaío [South Aegean]',
			'PEL' => 'Pelopónnisos [Peloponnese]',
			'SEL' => 'Stereá Elláda [Central Greece]',
			'THE' => 'Thessalía [Thessaly]',
			'VAI' => 'Vório Aigaío [Northern Aegean]'
		),
		'HK' => array(
			'HONG KONG' => 'Hong Kong Island',
			'KOWLOONG' => 'Kowloong',
			'NEW TERRITORIES' => 'New Territories'
		),
		// Hungary: Counties
		'HU' => array(
			'BAC' => 'Bács-Kiskun',
			'BAR' => 'Baranya',
			'BEK' => 'Békés',
			'BOR' => 'Borsod-Abaúj-Zemplén',
			'BUD' => 'Budapest',
			'CSO' => 'Csongrád',
			'FEJ' => 'Fejér',
			'GYO' => 'Gyor-Moson-Sopron',
			'HAJ' => 'Hajdú-Bihar',
			'HEV' => 'Heves',
			'JAS' => 'Jász-Nagykun-Szolnok',
			'KOM' => 'Komárom-Esztergom',
			'NOG' => 'Nógrád',
			'PES' => 'Pest',
			'SOM' => 'Somogy',
			'SZA' => 'Szabolcs-Szatmár-Bereg',
			'TOL' => 'Tolna',
			'VAS' => 'Vas',
			'VES' => 'Veszprém',
			'ZAL' => 'Zala'
		),
		// Ireland: Counties
		'IE' => array(
			'G' => 'Galway (incl. Galway City)',
			'LM' => 'Leitrim',
			'MO' => 'Mayo',
			'RN' => 'Roscommon',
			'SO' => 'Sligo',
			'CW' => 'Carlow',
			'D' => 'Dublin',
			'DR' => 'Dún Laoghaire-Rathdown',
			'FG' => 'Fingal',
			'KE' => 'Kildare',
			'KK' => 'Kilkenny',
			'LS' => 'Laois',
			'LD' => 'Longford',
			'LH' => 'Louth',
			'MH' => 'Meath',
			'OY' => 'Offaly',
			'SD' => 'South Dublin',
			'WH' => 'Westmeath',
			'WX' => 'Wexford',
			'WW' => 'Wicklow',
			'CE' => 'Clare',
			'C' => 'Cork (incl. Cork City)',
			'KY' => 'Kerry',
			'LK' => 'Limerick (incl. Limerick City)',
			'NT' => 'North Tipperary',
			'ST' => 'South Tipperary',
			'WD' => 'Waterford (incl. Waterford City)',
			'CN' => 'Cavan',
			'DL' => 'Donegal',
			'MIN' => 'Monaghan'
		),
		// Netherlands: Provinces
		'NL' => array(
			'D' => 'Drenthe',
			'Fl' => 'Flevoland',
			'Fr' => 'Friesland',
			'Gld' => 'Gelderland',
			'Gr' => 'Groningen',
			'L' => 'Limburg',
			'N-B' => 'Noord-Brabant',
			'N-H' => 'Noord-Holland',
			'O' => 'Overijssel',
			'U' => 'Utrecht',
			'Z' => 'Zeeland',
			'Z-H' => 'Zuid-Holland'
		),
		// New Zealand: Regions
		'NZ' => array(
			'AUK' => 'Auckland',
			'BOP' => 'Bay of Plenty',
			'CAN' => 'Canterbury',
			'GIS' => 'Gisborne',
			'HKB' => 'Hawke\'s Bay',
			'MWT' => 'Manawatu-Wanganui',
			'MBH' => 'Marlborough',
			'NSN' => 'Nelson',
			'NTL' => 'Northland',
			'OTA' => 'Otago',
			'STL' => 'Southland',
			'TKI' => 'Taranaki',
			'TAS' => 'Tasman',
			'WKO' => 'Waikato',
			'WGN' => 'Wellington',
			'WTC' => 'West Coast'
		),
		// Romania: Counties
		'RO' => array(
			'ALB' => 'Alba',
			'ARA' => 'Arad',
			'ARG' => 'Argeș',
			'BAC' => 'Bacău',
			'BIH' => 'Bihor',
			'BIS' => 'Bistrița-Năsăud',
			'BOT' => 'Botoșani',
			'BRA' => 'Brăila',
			'BRS' => 'Brașov',
			'BUC' => 'București',
			'BUZ' => 'Buzău',
			'CAL' => 'Călărași',
			'CAR' => 'Caraș-Severin',
			'CLU' => 'Cluj',
			'CON' => 'Constanța',
			'COV' => 'Covasna',
			'DAM' => 'Dâmbovița',
			'DOL' => 'Dolj',
			'GAL' => 'Galați',
			'GIU' => 'Giurgiu',
			'GOR' => 'Gorj',
			'HAR' => 'Harghita',
			'HUN' => 'Hunedoara',
			'IAL' => 'Ialomița',
			'IAS' => 'Iași',
			'ILF' => 'Ilfov',
			'MAR' => 'Maramureș',
			'MEH' => 'Mehedinți',
			'MUR' => 'Mureș',
			'NEA' => 'Neamț',
			'OLT' => 'Olt',
			'PRA' => 'Prahova',
			'SAL' => 'Sălaj',
			'SAT' => 'Satu Mare',
			'SIB' => 'Sibiu',
			'SUC' => 'Suceava',
			'TEL' => 'Teleorman',
			'TIM' => 'Timiș',
			'TUL' => 'Tulcea',
			'VAL' => 'Vâlcea',
			'VAS' => 'Vaslui',
			'VRA' => 'Vrancea'
		),
		// Serbia: Districts
		'SR' => array(
			'BOR' => 'Bor',
			'BRA' => 'Branicevo',
			'GBE' => 'Grad Beograd',
			'JAB' => 'Jablanica',
			'KOL' => 'Kolubara',
			'MAC' => 'Macva',
			'MOR' => 'Moravica',
			'NIS' => 'Nišava',
			'PCI' => 'Pcinja',
			'PIR' => 'Pirot',
			'POD' => 'Podunavlje [Danube]',
			'POM' => 'Pomoravlje',
			'RSN' => 'Rasina',
			'RSK' => 'Raška',
			'SUM' => 'Šumadija',
			'TOP' => 'Toplica',
			'ZAJ' => 'Zajecar',
			'ZLA' => 'Zlatibor',
			'JBK' => 'Južna Backa',
			'JBN' => 'Južni Banat',
			'SBK' => 'Severna Backa',
			'SBN' => 'Severni Banat',
			'SRB' => 'Srednji Banat',
			'SRE' => 'Srem',
			'ZBK' => 'Zapadna Backa [West Backa]'
		),
		// Sweden: Counties ("län")
		'SE' => array(
			'BLE' => 'Blekinge län',
			'DAL' => 'Dalarnas län',
			'GAV' => 'Gävleborgs län',
			'GOT' => 'Gotlands län',
			'HAL' => 'Hallands län',
			'JAM' => 'Jämtlands län',
			'JON' => 'Jönköpings län',
			'KAL' => 'Kalmar län',
			'KRO' => 'Kronobergs län',
			'NOR' => 'Norrbottens län',
			'ORE' => 'Örebro län',
			'OST' => 'Östergötlands län',
			'SKA' => 'Skåne län',
			'SOD' => 'Södermanlands län',
			'STO' => 'Stockholms län',
			'UPP' => 'Uppsala län',
			'VAR' => 'Värmlands län',
			'VAS' => 'Västerbottens län',
			'VNL' => 'Västernorrlands län',
			'VML' => 'Västmanlands län',
			'VGO' => 'Västra Götalands län'
		),
		'US' => array(
			'AK' => 'Alaska',
			'AL' => 'Alabama',
			'AR' => 'Arkansas',
			'AZ' => 'Arizona',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DC' => 'District Of Columbia',
			'DE' => 'Delaware',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'IA' => 'Iowa',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'MA' => 'Massachusetts',
			'MD' => 'Maryland',
			'ME' => 'Maine',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MO' => 'Missouri',
			'MS' => 'Mississippi',
			'MT' => 'Montana',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'NE' => 'Nebraska',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NV' => 'Nevada',
			'NY' => 'New York',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VA' => 'Virginia',
			'VT' => 'Vermont',
			'WA' => 'Washington',
			'WI' => 'Wisconsin',
			'WV' => 'West Virginia',
			'WY' => 'Wyoming'
		),
		'USAF' => array(
			'AA' => 'Americas',
			'AE' => 'Europe',
			'AP' => 'Pacific'
		)
	);

	protected static $euCountries = array(
		'AT' => 'Austria',
		'BE' => 'Belgium',
		'BG' => 'Bulgaria',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'EE' => 'Estonia',
		'FI' => 'Finland',
		'FR' => 'France',
		'DE' => 'Germany',
		'GR' => 'Greece',
		'HU' => 'Hungary',
		'IE' => 'Ireland',
		'IT' => 'Italy',
		'LV' => 'Latvia',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MT' => 'Malta',
		'NL' => 'Netherlands',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'RO' => 'Romania',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'ES' => 'Spain',
		'SE' => 'Sweden',
		'GB' => 'United Kingdom'
	);

	/** @var Options */
	private static $options;
	private static $cache = array();

	/**
	 * @param Options $options Options object.
	 */
	public static function setOptions($options)
	{
		self::$options = $options;
	}

	/**
	 * Returns list of available countries with translated names.
	 *
	 * Safe to use multiple times (uses cache to speed-up).
	 *
	 * @return array List of translated countries.
	 */
	public static function getAll()
	{
		if (!isset(self::$cache['countries'])) {
			$countries = array_map(function($item){ return __($item, 'jigoshop'); }, self::$countries);
			asort($countries, SORT_LOCALE_STRING);
			self::$cache['countries'] = $countries;
		}

		return self::$cache['countries'];
	}


	/**
	 * Returns list of all allowed countries with translated names.
	 *
	 * Safe to use multiple times (uses cache to speed-up).
	 *
	 * @return array List of allowed translated countries.
	 */
	public function getAllowed()
	{
		if (!isset(self::$cache['allowed'])) {
			$countries = self::getAll();

			if (self::$options->get('shopping.restrict_selling_locations')) {
				$allowed = self::$options->get('shopping.selling_locations');
				$countries = array_intersect_key($countries, array_flip($allowed));
			}

			self::$cache['allowed'] = $countries;
		}

		return self::$cache['allowed'];
	}

	/**
	 * Returns translated name of a country.
	 *
	 * If country does not exists - returns empty string.
	 *
	 * @param $countryCode string Country code for name.
	 * @return string Country translated name.
	 */
	public static function getName($countryCode)
	{
		if (self::exists($countryCode)) {
			$all = self::getAll();
			return $all[$countryCode];
		}

		return '';
	}

	/**
	 * @param $countryCode string Country code to check.
	 * @return bool Whether the country exists.
	 */
	public static function exists($countryCode)
	{
		return isset(self::$countries[$countryCode]);
	}

	/**
	 * @param $countryCode string Country code to check.
	 * @return bool Whether the country is allowed.
	 */
	public static function isAllowed($countryCode)
	{
		$allowed = self::getAllowed();
		return isset($allowed[$countryCode]);
	}

	public static function getStateName($countryCode, $stateCode)
	{
		if (!self::hasState($countryCode, $stateCode)) {
			return $stateCode;
		}

		return self::$states[$countryCode][$stateCode];
	}

	/**
	 * Returns list of all defined states.
	 *
	 * @return array List of all states.
	 */
	public static function getAllStates()
	{
		return self::$states;
	}

	/**
	 * Returns list of states defined for selected country.
	 *
	 * If country has no states - empty array is returned.
	 *
	 * @param $countryCode string Country code to fetch data for.
	 * @return array List of states.
	 */
	public static function getStates($countryCode)
	{
		if (self::hasStates($countryCode)) {
			return self::$states[$countryCode];
		}

		return array();
	}

	/**
	 * @param $countryCode string Country code to check.
	 * @return bool Whether the country has defined states.
	 */
	public static function hasStates($countryCode)
	{
		return isset(self::$states[$countryCode]);
	}

	/**
	 * @param $countryCode string Country code to check.
	 * @param $stateCode string State code to check.
	 * @return bool Whether the country has defined states.
	 */
	public static function hasState($countryCode, $stateCode)
	{
		return self::hasStates($countryCode) && isset(self::$states[$countryCode][$stateCode]);
	}

	/**
	 * @param $countryCode string Country code to check.
	 * @return bool Whether the country is from European Union.
	 */
	public static function isEU($countryCode)
	{
		return isset(self::$euCountries[$countryCode]);
	}
}
