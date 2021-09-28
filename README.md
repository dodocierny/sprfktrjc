# SuperFaktura

V súbore `ulohy.txt` je pôvodné zadanie. Vypracovanie je rozdelené do osobitných adresárov pre 
každú úlohu.

## Zadanie č. 1

### Riešenie

Zvolil som vypracovanie priamo v PHP. Riešenie sa achádza v adresári `Zadanie1`, do ktorého je potrebné 
sa prepnúť a nainštalovať  balíčky - pre testovanie výstupu je napísaný jednoduchý unit test a composer 
použijem aj na autoloading. 

Inštalácia balíčkov:

```shell
composer install
```

Zobrazenie výsledku riešenia:

```shell
php zadanie1.php
```
Spustenie testu:

```shell
vendor/bin/phpunit SuperFakturaTest.php
```

Vychádzal som zo vzorových dát (ako má vyzerať výstup), kde pre vstupnú hodnotu *15* bolo vo výpise uvedené 
*SuperFaktura* - číslo 15 je deliteľné jednak samo sebou, jednak 5 aj 3 - ale výpis 
má byť SuperFaktura, t.j. zvolil som nasledovné poradie podmienok kontrolovaných 
v cykle - po splnení podmienky sa cyklus posunie na ďalšiu iteráciu.

Získané dáta z `$data` sa následne vypíšu v požadovanom formáte (jedna hodnota na riadku) 
metódou `print()` z triedy `SuperFaktura`.

Navrhnutý algoritmus:

```php
$data = [];
for($i = 1; $i <= 100; $i++) {

    if($i % 15 === 0) {
        $data[] = "SuperFaktura";
        continue;
    } else if($i % 5 === 0) {
        $data[] = "Faktura";
        continue;
    } else if($i % 3 === 0) {
        $data[] = "Super";
        continue;
    } else {
        $data[] = strval($i);
    }
}
```

Existuje viac riešení, iný spôsob s rovnakým výsledkom:

```php
$data = [];
for($i = 1; $i <= 100; $i++) {

    $result = "";

    if($i % 3 === 0) {
        $result .= "Super";
    }

    if($i % 5 === 0) {
        $result .= "Faktura";
    }

    if($i % 3 !== 0 && $i % 5 !== 0) {
        $result .= $i;
    }

    $data[] = $result;
}
```

Kompletný kód je v súbore `SuperFaktura.php`, skript `zadanie1.php` slúži pre zobrazenie výstupu. Súbor
`SuperFakturaTest.php` obsahuje test.

## Zadanie č. 2

Ako riešenie som zvolil nasledovný kód pre výber duplicít:

```mariadb
SELECT
    dupl.id,
    dupl.value
FROM
    duplicates AS dupl
INNER JOIN
    (SELECT
        dupval.value
    FROM
        duplicates dupval
    GROUP BY
        dupval.value
    HAVING
        COUNT(*) > 1
    ) AS dupval ON dupval.value = dupl.value;
```

Pri overení správnosti sa výstup zhoduje s požadovaným ak v tabuľke `duplicates` sú hodnoty zo zadania.

Ak by sa v tabuľke `duplicates` nachádzalo veľké množstvo dát navrhujem nad stĺpcom
`value` vytvoriť index, ktorý bude efektívne použitý pri `GROUP BY`. 

Celé riešenie sa nachádza v adresáry `Zadanie2` v súbore `zadanie2.sql`, kde je aj popísané
testovanie varianty tabuľky s indexom a bez a pri 10 000 000 záznamoch v oboch tabuľkách
s nasledovným záverom:

Pri malom počte riadkov  je rozdiel pri výbere duplicít medzi tabuľkou bez indexu nad stĺpcom
s duplicitami a tabuľkou  s indexom nad týmto stĺpcom malý. Z rastúcim počtom záznamov index pomože
zásadne zvýšiť rýchlosť výberu údajov. Použitie indexu v testovacom prípade stálo cca 150MB priestoru
na disku, ale skráti výber údajo niekoľko tisíc násobne.

Výpis z logu pri testovaní - spustenie dotazu podľa zadania - tabuľka bez indexu:

```shell
[2021-09-28 09:00:45] 500 rows retrieved starting from 1 in 47 s 777 ms (execution: 47 s 755 ms, fetching: 22 ms)
```

Tabuľka s indexom:

```shell
[2021-09-28 09:01:05] 500 rows retrieved starting from 1 in 166 ms (execution: 14 ms, fetching: 152 ms)
```

V adresáry je priložený `docker-compose.yml` pre vytvorenie databázy, tabuliek a zároveň naplnenie 
testovacími dátami - preto jeho spustenie trvá dlhšie, rádovo minúty - pre skrátenie je možné 
zakomentovať volanie `RandNum` v `run.sql`. Je použitá MariaDB, názov databázy `superfaktura_test`, port `8306`.

Spustenie:

```shell
docker-compose up
```

## Zadanie č. 3

Vypracované zadanie sa nachádza v adresáry `Zadanie3`. Využil som composer pre autoloading. V adresári
je najskôr potrebné spustiť:

```shell
composer install
```

Overenie IČO voči registru - zavolá sa skript `zadanie3.php` s argumentom IČO:

```shell
php zadanie3.php $ARG=ICO
```

Príklad:

```shell
php zadanie3.php 01569651
```

Vráti výstup:

> CN: Superfaktura.cz, s.r.o. ICO: 01569651 DIC: CZ01569651 LF: Společnost s ručením omezeným CRE: 2013-04-08

### Popis riešenia

V adresáry `core` sa nachádzajú jednotlivé triedy:

* `Company` je pomocná trieda, ktorá reprezentuje jednoduchú entitu firmy
* v adresáry `Exceptions` sa nachádzajú rôzne typy výnimiek, ktoré sa môžu
vyskytnúť a ktoré sú v skripte `zadanie3.php` zachytené
* hlavný kód je v adresáry `Reader` v triede `Ares`, ktorá má na starosti
získavanie údajov z registra firiem
* využíva pomocnú triedu `UrlReader`, ktorá je myslená ako rodičovská trieda
pre ďalšie triedy pre napojenie na rôzne typy registrov dostupných na nejakej webovej adrese a ktorej
sa nachádza spoločná funkcionalita pre prístup na registre (napr. IČO validator).

Je ošetrených viacero chybových stavov, ktoré môžu nastať:

* validuje sa IČO - použil som existujúci kód s referenciou v komentáry triedy
* jeho zadanie pri volaní skriptu
* nenájdené IČO
* kontroluje sa tiež dostupnosť ARESu a tiež štruktúra odpovede - pomohol som si opäť existujúcim
kódom s uvedenou referenciou pre komunikáciu s ARESom a parsovanie odpovede

