<?php

use App\Console\Commands\Questions\Prep;
use Illuminate\Support\Facades\Artisan;

it('extracts level-2 sections from a standard-style document', function () {
    $md = <<<'TXT'
Inhaltsverzeichnis

1     Einleitung
   1.1    Versionshistorie . . . . . . . . . . . . . . . . . . .    5
   1.2    Zielsetzung . . . . . . . . . . . . . . . . . . . . . .   5

4

                                                1 Einleitung
1       Einleitung

1.1     Versionshistorie
Der BSI-Standard 200-1 löst den BSI-Standard 100-1 ab.

5

1.2     Zielsetzung
Die zunehmende Digitalisierung und Vernetzung der Arbeitswelt stellt Unternehmen und Behörden
heute vor grundlegende Herausforderungen.

6

1.3     Adressatenkreis
Dieser BSI-Standard 200-1 richtet sich primär an Verantwortliche für die Informationssicherheit.
TXT;

    $sections = app(Prep::class)->parseStandard($md);

    expect($sections)->toHaveCount(3)
        ->and($sections[0]['chapter'])->toBe('1.1')
        ->and($sections[0]['title'])->toBe('Versionshistorie')
        ->and($sections[1]['chapter'])->toBe('1.2')
        ->and($sections[1]['title'])->toBe('Zielsetzung')
        ->and($sections[2]['chapter'])->toBe('1.3');
});

it('rejects TOC entries that contain dot leaders', function () {
    $md = "Inhaltsverzeichnis\n\n1.1    Foo . . . . . . . . . . . .   5\n\n4\n\n1.1     Foo\nContent of Foo.\n";

    $sections = app(Prep::class)->parseStandard($md);

    expect($sections)->toHaveCount(1)
        ->and($sections[0]['title'])->toBe('Foo');
});

it('rejects mid-sentence fragments that happen to start with a digit pattern', function () {
    $md = <<<'TXT'
Inhaltsverzeichnis

1     Einleitung
1.1    Versionshistorie

4

1.1    Versionshistorie
Dies bezieht sich auf
27001 auf eine explizite Nennung des PDCA-Zyklus verzichtet worden.
Weitere Erläuterungen folgen.
TXT;

    $sections = app(Prep::class)->parseStandard($md);

    expect(array_column($sections, 'chapter'))->not->toContain('27001');
});

it('extracts Baustein sections from a Kompendium-style document', function () {
    $md = <<<'TXT'
Inhaltsverzeichnis

SYS.1.1 Allgemeiner Server                    R2            IT-System

SYS.1.1                                                                                             SYS.1: Server

SYS.1.1 Allgemeiner Server

1. Beschreibung
1.1. Einleitung
Als „Allgemeiner Server" werden IT-Systeme mit einem beliebigen Betriebssystem bezeichnet.

2                                                                    IT-Grundschutz-Kompendium: Stand Februar 2023

APP.3.1 Webanwendungen und Webservices

1. Beschreibung
1.1. Einleitung
Webanwendungen stellen Funktionen und dynamische Inhalte über das HTTP/HTTPS-Protokoll bereit.
TXT;

    $sections = app(Prep::class)->parseKompendium($md);

    expect($sections)->toHaveCount(2)
        ->and($sections[0]['chapter'])->toBe('SYS.1.1')
        ->and($sections[0]['title'])->toBe('Allgemeiner Server')
        ->and($sections[0]['page_start'])->toBe(1)
        ->and($sections[1]['chapter'])->toBe('APP.3.1')
        ->and($sections[1]['title'])->toBe('Webanwendungen und Webservices');
});

it('rejects Kompendium running headers referencing the parent Schicht', function () {
    $md = <<<'TXT'
Inhaltsverzeichnis

SYS.1.1                                                                                             SYS.1: Server

SYS.1.1 Allgemeiner Server

1. Beschreibung
Als „Allgemeiner Server" werden IT-Systeme bezeichnet.
TXT;

    $sections = app(Prep::class)->parseKompendium($md);

    expect($sections)->toHaveCount(1)
        ->and($sections[0]['title'])->toBe('Allgemeiner Server');
});

it('requires "Beschreibung" to follow a Kompendium candidate heading', function () {
    $md = <<<'TXT'
Inhaltsverzeichnis

SYS.1.1 Allgemeiner Server

Ein Absatz ohne die erwartete Beschreibungs-Einleitung. Kein Treffer.
TXT;

    $sections = app(Prep::class)->parseKompendium($md);

    expect($sections)->toHaveCount(0);
});

it('fails with an unknown document argument', function () {
    $exit = Artisan::call('questions:prep', ['document' => 'bogus']);

    expect($exit)->toBe(1);
});
