<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Database\Seeder;

class BsiStandard2001QuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $module = Module::firstOrCreate(
            ['slug' => 'm2-bsi-grundschutz'],
            [
                'name' => 'M2 - BSI Grundschutz',
                'description' => 'Fragen zum BSI IT-Grundschutz und den zugehörigen Standards.',
            ]
        );

        $questions = [
            // === Kapitel 1: Einleitung ===
            [
                'text' => 'Welchen BSI-Standard löst der BSI-Standard 200-1 ab?',
                'explanation' => 'Der BSI-Standard 200-1 löst den BSI-Standard 100-1 ab. Die Version 1.0 wurde im Oktober 2017 veröffentlicht und enthält Anpassungen an die Fortschreibung der ISO-Normen sowie an den BSI-Standard 200-2.',
                'quote' => 'Der BSI-Standard 200-1 löst den BSI-Standard 100-1 ab.',
                'source' => 'BSI-Standard 200-1, Kapitel 1.1, S. 5',
                'answers' => [
                    ['text' => 'BSI-Standard 100-1', 'is_correct' => true],
                    ['text' => 'BSI-Standard 100-2', 'is_correct' => false],
                    ['text' => 'BSI-Standard 200-2', 'is_correct' => false],
                    ['text' => 'BSI-Standard 100-4', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussage beschreibt das Verhältnis von IT-Sicherheit und Informationssicherheit korrekt?',
                'explanation' => 'IT-Sicherheit ist eine Teilmenge der Informationssicherheit. Informationssicherheit schützt Informationen jeglicher Art — auf Papier, in IT-Systemen oder in den Köpfen der Benutzer. IT-Sicherheit konzentriert sich hingegen nur auf den Schutz elektronisch gespeicherter Informationen.',
                'quote' => 'IT-Sicherheit als Teilmenge der Informationssicherheit konzentriert sich auf den Schutz elektronisch gespeicherter Informationen und deren Verarbeitung.',
                'source' => 'BSI-Standard 200-1, Kapitel 2, S. 8',
                'answers' => [
                    ['text' => 'IT-Sicherheit ist eine Teilmenge der Informationssicherheit', 'is_correct' => true],
                    ['text' => 'Informationssicherheit ist eine Teilmenge der IT-Sicherheit', 'is_correct' => false],
                    ['text' => 'IT-Sicherheit und Informationssicherheit sind identische Begriffe', 'is_correct' => false],
                    ['text' => 'IT-Sicherheit umfasst auch den Schutz von Papierakten', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche weiteren generischen Oberbegriffe der Informationssicherheit werden neben den klassischen Grundwerten genannt?',
                'explanation' => 'Neben den klassischen Grundwerten Vertraulichkeit, Integrität und Verfügbarkeit werden als weitere Oberbegriffe Authentizität, Verbindlichkeit, Zuverlässigkeit, Resilienz und Nichtabstreitbarkeit genannt. Diese können je nach Anwendungsfall ergänzend betrachtet werden.',
                'quote' => 'Weitere generische Oberbegriffe der Informationssicherheit sind beispielsweise Authentizität, Verbindlichkeit, Zuverlässigkeit, Resilienz und Nichtabstreitbarkeit.',
                'source' => 'BSI-Standard 200-1, Kapitel 2, S. 8',
                'answers' => [
                    ['text' => 'Authentizität und Nichtabstreitbarkeit', 'is_correct' => true],
                    ['text' => 'Resilienz und Zuverlässigkeit', 'is_correct' => true],
                    ['text' => 'Verbindlichkeit', 'is_correct' => true],
                    ['text' => 'Performanz und Skalierbarkeit', 'is_correct' => false],
                ],
            ],
            // === Kapitel 2: Normen und Standards ===
            [
                'text' => 'Was zeichnet die ISO/IEC 27001 als Norm für Informationssicherheit aus?',
                'explanation' => 'Die ISO/IEC 27001 ist eine internationale Norm, die eine Zertifizierung ermöglicht. Sie gibt auf ca. neun Seiten normative Vorgaben zur Einführung, dem Betrieb und der Verbesserung eines ISMS. In einem normativen Anhang werden mehr als 100 Maßnahmen (Controls) aufgeführt.',
                'quote' => 'ISO/IEC 27001 ist eine internationale Norm zum Management von Informationssicherheit, die auch eine Zertifizierung ermöglicht. ISO/IEC 27001 gibt ca. neun Seiten normative Vorgaben zur Einführung, dem Betrieb und der Verbesserung eines dokumentierten Informationssicherheitsmanagementsystems.',
                'source' => 'BSI-Standard 200-1, Kapitel 2.1.1, S. 9',
                'answers' => [
                    ['text' => 'Sie ermöglicht eine Zertifizierung des ISMS', 'is_correct' => true],
                    ['text' => 'Sie enthält ca. neun Seiten normative Vorgaben', 'is_correct' => true],
                    ['text' => 'Sie enthält in einem normativen Anhang mehr als 100 Maßnahmen (Controls)', 'is_correct' => true],
                    ['text' => 'Sie beschreibt konkrete technische Konfigurationen für IT-Systeme', 'is_correct' => false],
                    ['text' => 'Sie ist nur für deutsche Behörden verbindlich', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche BSI-Standards gehören zur BSI-Standardreihe zum Thema IS-Management?',
                'explanation' => 'Die BSI-Standardreihe umfasst: 200-1 (Managementsysteme für Informationssicherheit), 200-2 (IT-Grundschutz-Methodik), 200-3 (Risikoanalyse auf der Basis von IT-Grundschutz) und den noch nicht aktualisierten 100-4 (Notfallmanagement). Der Standard 200-4 war zum Zeitpunkt der Veröffentlichung noch nicht erschienen.',
                'quote' => 'BSI-Standard 200-1: Managementsysteme für Informationssicherheit (ISMS). BSI-Standard 200-2: IT-Grundschutz-Methodik. BSI-Standard 200-3: Risikoanalyse auf der Basis von IT-Grundschutz. BSI-Standard 100-4: Notfallmanagement.',
                'source' => 'BSI-Standard 200-1, Kapitel 2.1.2, S. 11-12',
                'answers' => [
                    ['text' => '200-1: Managementsysteme für Informationssicherheit (ISMS)', 'is_correct' => true],
                    ['text' => '200-2: IT-Grundschutz-Methodik', 'is_correct' => true],
                    ['text' => '200-3: Risikoanalyse auf der Basis von IT-Grundschutz', 'is_correct' => true],
                    ['text' => '200-4: Cloud-Sicherheit', 'is_correct' => false],
                ],
            ],
            // === Kapitel 3: ISMS-Definition und Prozessbeschreibung ===
            [
                'text' => 'Aus welchen grundlegenden Komponenten besteht ein ISMS laut BSI-Standard 200-1?',
                'explanation' => 'Ein ISMS besteht aus vier grundlegenden Komponenten: Managementprinzipien, Ressourcen, Mitarbeiter und dem Sicherheitsprozess. Der Sicherheitsprozess wiederum umfasst die Leitlinie zur Informationssicherheit, das Sicherheitskonzept und die Sicherheitsorganisation.',
                'quote' => 'Zu einem ISMS gehören folgende grundlegende Komponenten: Managementprinzipien, Ressourcen, Mitarbeiter, Sicherheitsprozess',
                'source' => 'BSI-Standard 200-1, Kapitel 3.1, S. 15',
                'answers' => [
                    ['text' => 'Managementprinzipien', 'is_correct' => true],
                    ['text' => 'Ressourcen', 'is_correct' => true],
                    ['text' => 'Mitarbeiter', 'is_correct' => true],
                    ['text' => 'Sicherheitsprozess', 'is_correct' => true],
                    ['text' => 'Externe Auditoren', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was legt das ISMS laut BSI-Standard 200-1 konkret fest?',
                'explanation' => 'Das ISMS definiert, mit welchen Instrumenten und Methoden die Leitungsebene die auf Informationssicherheit ausgerichteten Aufgaben und Aktivitäten nachvollziehbar lenkt — also plant, einsetzt, durchführt, überwacht und verbessert.',
                'quote' => 'Das ISMS legt fest, mit welchen Instrumenten und Methoden die Leitungsebene die auf Informationssicherheit ausgerichteten Aufgaben und Aktivitäten nachvollziehbar lenkt (plant, einsetzt, durchführt, überwacht und verbessert).',
                'source' => 'BSI-Standard 200-1, Kapitel 3.1, S. 15',
                'answers' => [
                    ['text' => 'Mit welchen Instrumenten und Methoden die Leitungsebene IS-Aufgaben nachvollziehbar lenkt', 'is_correct' => true],
                    ['text' => 'Welche konkreten IT-Produkte die Institution einsetzen muss', 'is_correct' => false],
                    ['text' => 'Welche Mitarbeiter entlassen werden, wenn ein Sicherheitsvorfall eintritt', 'is_correct' => false],
                    ['text' => 'Die technischen Spezifikationen aller eingesetzten Firewalls', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Phasen umfasst der PDCA-Zyklus (Lebenszyklus nach Deming) im Sicherheitsprozess?',
                'explanation' => 'Der PDCA-Zyklus besteht aus vier Phasen: 1. Planung (Plan), 2. Umsetzung der Planung bzw. Durchführung des Vorhabens (Do), 3. Erfolgskontrolle bzw. Überwachung der Zielerreichung (Check) und 4. Beseitigung von erkannten Mängeln und Schwächen, Optimierung sowie Verbesserung (Act).',
                'quote' => 'Dieses Modell wird nach der englischen Benennung der einzelnen Phasen („Plan", „Do", „Check", „Act") entsprechend auch als PDCA-Zyklus bezeichnet.',
                'source' => 'BSI-Standard 200-1, Kapitel 3.2.2, S. 18',
                'answers' => [
                    ['text' => 'Plan: Planung und Konzeption', 'is_correct' => true],
                    ['text' => 'Do: Umsetzung der Planung', 'is_correct' => true],
                    ['text' => 'Check: Erfolgskontrolle und Überwachung der Zielerreichung', 'is_correct' => true],
                    ['text' => 'Act: Optimierung und Verbesserung', 'is_correct' => true],
                    ['text' => 'Review: Externe Begutachtung durch Auditoren', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Wer gibt die Sicherheitsstrategie vor und worauf basiert sie?',
                'explanation' => 'Die Sicherheitsstrategie wird von der Leitungsebene vorgegeben und basiert auf den Geschäftszielen des Unternehmens bzw. dem Auftrag der Behörde. Sie dient der Orientierung, um die gesetzten Sicherheitsziele zu erreichen.',
                'quote' => 'Die Sicherheitsstrategie dient der Orientierung für die Planung des weiteren Vorgehens, um die gesetzten Sicherheitsziele zu erreichen. Die Strategie wird von der Leitungsebene vorgegeben und basiert auf den Geschäftszielen des Unternehmens bzw. dem Auftrag der Behörde.',
                'source' => 'BSI-Standard 200-1, Kapitel 3.1, S. 16',
                'answers' => [
                    ['text' => 'Die Leitungsebene gibt die Strategie vor', 'is_correct' => true],
                    ['text' => 'Sie basiert auf den Geschäftszielen des Unternehmens bzw. dem Auftrag der Behörde', 'is_correct' => true],
                    ['text' => 'Der ISB definiert die Strategie eigenständig', 'is_correct' => false],
                    ['text' => 'Sie basiert ausschließlich auf technischen Anforderungen', 'is_correct' => false],
                ],
            ],
            // === Kapitel 4: Management-Prinzipien ===
            [
                'text' => 'Welche Aufgaben und Pflichten hat die Leitungsebene bezüglich Informationssicherheit laut BSI-Standard 200-1?',
                'explanation' => 'Die Leitungsebene hat sechs zentrale Aufgaben: 1. Übernahme der Gesamtverantwortung, 2. Informationssicherheit initiieren, steuern und kontrollieren, 3. Informationssicherheit integrieren, 4. Erreichbare Ziele setzen, 5. Sicherheitskosten gegen Nutzen abwägen, 6. Vorbildfunktion übernehmen.',
                'quote' => 'Die Aufgaben und Pflichten der Leitungsebene bezüglich Informationssicherheit lassen sich in folgenden Punkten zusammenfassen: 1. Übernahme der Gesamtverantwortung für Informationssicherheit [...] 2. Informationssicherheit initiieren, steuern und kontrollieren [...] 3. Informationssicherheit integrieren [...] 4. Erreichbare Ziele setzen [...] 5. Sicherheitskosten gegen Nutzen abwägen [...] 6. Vorbildfunktion',
                'source' => 'BSI-Standard 200-1, Kapitel 4.1, S. 20-22',
                'answers' => [
                    ['text' => 'Übernahme der Gesamtverantwortung für Informationssicherheit', 'is_correct' => true],
                    ['text' => 'Informationssicherheit integrieren und erreichbare Ziele setzen', 'is_correct' => true],
                    ['text' => 'Sicherheitskosten gegen Nutzen abwägen und Vorbildfunktion übernehmen', 'is_correct' => true],
                    ['text' => 'Eigenständige Durchführung aller technischen Sicherheitsmaßnahmen', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussage trifft auf die Verantwortung der Leitungsebene bei fehlenden Ressourcen zu?',
                'explanation' => 'Wenn Zielvorgaben aufgrund fehlender Ressourcen nicht erfüllt werden können, sind nicht die mit der Umsetzung betrauten Personen verantwortlich, sondern die Vorgesetzten, die unrealistische Ziele gesetzt bzw. die erforderlichen Ressourcen nicht zur Verfügung gestellt haben.',
                'quote' => 'Wenn die Zielvorgaben aufgrund fehlender Ressourcen nicht erfüllt werden können, sind hierfür nicht die mit der Umsetzung betrauten Personen verantwortlich, sondern die Vorgesetzten, die unrealistische Ziele gesetzt bzw. die erforderlichen Ressourcen nicht zur Verfügung gestellt haben.',
                'source' => 'BSI-Standard 200-1, Kapitel 5, S. 26',
                'answers' => [
                    ['text' => 'Die Vorgesetzten tragen die Verantwortung, wenn unrealistische Ziele gesetzt oder Ressourcen fehlen', 'is_correct' => true],
                    ['text' => 'Die mit der Umsetzung betrauten Mitarbeiter sind verantwortlich', 'is_correct' => false],
                    ['text' => 'Niemand trägt Verantwortung, wenn die Ressourcen nicht ausreichen', 'is_correct' => false],
                    ['text' => 'Der externe Dienstleister ist verantwortlich', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Dokumentationsarten unterscheidet der BSI-Standard 200-1 im Sicherheitsprozess?',
                'explanation' => 'Es werden drei Dokumentationsarten unterschieden: 1. Technische Dokumentation und Dokumentation von Arbeitsabläufen (Zielgruppe: Experten), 2. Managementberichte (Zielgruppe: Leitungsebene, Sicherheitsmanagement) und 3. Aufzeichnung von Managemententscheidungen (Zielgruppe: Leitungsebene).',
                'quote' => 'Folgende Dokumentationsarten lassen sich unterscheiden: 1. Technische Dokumentation und Dokumentation von Arbeitsabläufen (Zielgruppe: Experten) [...] 2. Managementberichte (Zielgruppe: Leitungsebene, Sicherheitsmanagement) [...] 3. Aufzeichnung von Managemententscheidungen (Zielgruppe: Leitungsebene)',
                'source' => 'BSI-Standard 200-1, Kapitel 4.2, S. 23',
                'answers' => [
                    ['text' => 'Technische Dokumentation und Dokumentation von Arbeitsabläufen', 'is_correct' => true],
                    ['text' => 'Managementberichte für die Leitungsebene', 'is_correct' => true],
                    ['text' => 'Aufzeichnung von Managemententscheidungen', 'is_correct' => true],
                    ['text' => 'Öffentliche Pressemitteilungen über Sicherheitsvorfälle', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Warum sollen Audits NICHT von den Erstellern der Sicherheitsvorgaben durchgeführt werden?',
                'explanation' => 'Audits sollten nicht von denjenigen durchgeführt werden, die an der Planung oder Konzeption der Sicherheitsvorgaben beteiligt waren, da es schwierig ist, eigene Fehler zu finden. Je nach Größe der Institution kann es hilfreich sein, für Audits Externe hinzuzuziehen, um Betriebsblindheit zu vermeiden.',
                'quote' => 'Bei allen Audits sollte darauf geachtet werden, dass sie nicht von denjenigen durchgeführt werden, die an der Planung oder Konzeption von Sicherheitsvorgaben beteiligt waren, da es schwierig ist, eigene Fehler zu finden.',
                'source' => 'BSI-Standard 200-1, Kapitel 7.4, S. 31',
                'answers' => [
                    ['text' => 'Weil es schwierig ist, eigene Fehler zu finden', 'is_correct' => true],
                    ['text' => 'Um Betriebsblindheit zu vermeiden', 'is_correct' => true],
                    ['text' => 'Weil dies gesetzlich verboten ist', 'is_correct' => false],
                    ['text' => 'Weil Ersteller keine technischen Kenntnisse haben', 'is_correct' => false],
                ],
            ],
            // === Kapitel 6: Einbindung der Mitarbeiter ===
            [
                'text' => 'Welche Aussagen zur Einbindung der Mitarbeiter in den Sicherheitsprozess sind korrekt?',
                'explanation' => 'Informationssicherheit betrifft ohne Ausnahme alle Mitarbeiter. Sensibilisierung und Schulungen sind eine Grundvoraussetzung. Mitarbeiter müssen zur Einhaltung relevanter Gesetze und Regelungen verpflichtet werden und jeden erkannten oder vermuteten Sicherheitsvorfall unverzüglich melden.',
                'quote' => 'Die Informationssicherheit betrifft ohne Ausnahme alle Mitarbeiter. [...] Eine Sensibilisierung für Informationssicherheit und entsprechende Schulungen der Mitarbeiter sowie aller Führungskräfte sind daher eine Grundvoraussetzung für Informationssicherheit.',
                'source' => 'BSI-Standard 200-1, Kapitel 6, S. 27',
                'answers' => [
                    ['text' => 'Informationssicherheit betrifft ohne Ausnahme alle Mitarbeiter', 'is_correct' => true],
                    ['text' => 'Sensibilisierung und Schulungen sind eine Grundvoraussetzung', 'is_correct' => true],
                    ['text' => 'Nur IT-Mitarbeiter müssen in den Sicherheitsprozess eingebunden werden', 'is_correct' => false],
                    ['text' => 'Schulungen sind optional und nur bei konkreten Vorfällen nötig', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was muss bei der Einstellung neuer Mitarbeiter oder bei Änderungen der Zuständigkeiten in Bezug auf Informationssicherheit beachtet werden?',
                'explanation' => 'Bei der Einstellung oder neuen Aufgaben ist eine gründliche Einarbeitung mit Vermittlung sicherheitsrelevanter Aspekte notwendig. Beim Verlassen der Institution oder bei Zuständigkeitsänderungen muss der Prozess durch geeignete Sicherheitsmaßnahmen begleitet werden, wie z.B. Entzug von Berechtigungen, Rückgabe von Schlüsseln und Ausweisen.',
                'quote' => 'Werden Mitarbeiter neu eingestellt oder erhalten sie neue Aufgaben, ist eine gründliche Einarbeitung und gegebenenfalls Ausbildung notwendig. [...] Wenn Mitarbeiter die Institution verlassen oder sich ihre Zuständigkeiten verändern, muss dieser Prozess durch geeignete Sicherheitsmaßnahmen begleitet werden (z. B. Entzug von Berechtigungen, Rückgabe von Schlüsseln und Ausweisen).',
                'source' => 'BSI-Standard 200-1, Kapitel 6, S. 27',
                'answers' => [
                    ['text' => 'Gründliche Einarbeitung mit Vermittlung sicherheitsrelevanter Aspekte', 'is_correct' => true],
                    ['text' => 'Entzug von Berechtigungen und Rückgabe von Schlüsseln beim Ausscheiden', 'is_correct' => true],
                    ['text' => 'Sicherheitsaspekte erst nach der Probezeit vermitteln', 'is_correct' => false],
                    ['text' => 'Berechtigungen bleiben nach dem Ausscheiden erhalten', 'is_correct' => false],
                ],
            ],
            // === Kapitel 7: Der Sicherheitsprozess ===
            [
                'text' => 'Welche Themen sollten bei der Entwicklung der Sicherheitsstrategie laut BSI-Standard 200-1 mindestens berücksichtigt werden?',
                'explanation' => 'Bei der Entwicklung der Sicherheitsstrategie sollten mindestens berücksichtigt werden: Ziele des Unternehmens, gesetzliche Anforderungen und Vorschriften, Kundenanforderungen und Verträge, interne Rahmenbedingungen, IT-gestützte Geschäftsprozesse und Fachaufgaben sowie globale Bedrohungen der Geschäftstätigkeit.',
                'quote' => 'Folgende Themen sollten bei der Entwicklung der Sicherheitsstrategie mindestens berücksichtigt werden: Ziele des Unternehmens bzw. Aufgaben der Behörde, gesetzliche Anforderungen und Vorschriften, wie z. B. zum Datenschutz, Kundenanforderungen und bestehende Verträge, interne Rahmenbedingungen (z. B. organisationsweites Risikomanagement), (IT-gestützte) Geschäftsprozesse und Fachaufgaben und globale Bedrohungen der Geschäftstätigkeit durch Sicherheitsrisiken',
                'source' => 'BSI-Standard 200-1, Kapitel 7.1, S. 28',
                'answers' => [
                    ['text' => 'Ziele des Unternehmens und gesetzliche Anforderungen', 'is_correct' => true],
                    ['text' => 'Kundenanforderungen und interne Rahmenbedingungen', 'is_correct' => true],
                    ['text' => 'Globale Bedrohungen der Geschäftstätigkeit durch Sicherheitsrisiken', 'is_correct' => true],
                    ['text' => 'Detaillierte technische Konfigurationen aller Server', 'is_correct' => false],
                ],
            ],
            // === Kapitel 8: Sicherheitskonzept ===
            [
                'text' => 'Welche Schritte muss jede Risikoanalyse laut BSI-Standard 200-1 umfassen?',
                'explanation' => 'Jede Risikoanalyse muss sechs Schritte umfassen: Identifikation der zu schützenden Informationen und Geschäftsprozesse, Ermittlung aller relevanten Bedrohungen, Analyse der Schwachstellen, Benennung und Einschätzung möglicher Schäden, Untersuchung der Auswirkungen auf Geschäftstätigkeit und Aufgabenerfüllung, sowie Bewertung des Risikos.',
                'quote' => 'Jede Risikoanalyse muss die folgenden Schritte umfassen: Die zu schützenden Informationen und Geschäftsprozesse müssen identifiziert werden. Alle relevanten Bedrohungen [...] müssen ermittelt werden. Schwachstellen [...] müssen analysiert werden. Die möglichen Schäden durch den Verlust von Vertraulichkeit, Integrität oder Verfügbarkeit müssen benannt und eingeschätzt werden. Die anzunehmenden Auswirkungen auf die Geschäftstätigkeit [...] müssen untersucht werden. Das Risiko, durch Sicherheitsvorfälle Schäden zu erleiden, muss bewertet werden.',
                'source' => 'BSI-Standard 200-1, Kapitel 8.1, S. 34',
                'answers' => [
                    ['text' => 'Identifikation der zu schützenden Informationen und Geschäftsprozesse', 'is_correct' => true],
                    ['text' => 'Ermittlung aller relevanten Bedrohungen und Analyse der Schwachstellen', 'is_correct' => true],
                    ['text' => 'Bewertung des Risikos durch Sicherheitsvorfälle', 'is_correct' => true],
                    ['text' => 'Auswahl konkreter Sicherheitsprodukte und deren Beschaffung', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Optionen zur Behandlung von Risiken nennt der BSI-Standard 200-1?',
                'explanation' => 'Der BSI-Standard 200-1 nennt vier Optionen: Risiken können vermieden werden (Risikoursache ausschließen), reduziert werden (Rahmenbedingungen modifizieren), transferiert werden (z.B. durch Outsourcing oder Versicherungen) oder akzeptiert werden (auf Basis einer nachvollziehbaren Faktenlage). Die Art des Umgangs muss dokumentiert und von der Leitungsebene genehmigt werden.',
                'quote' => 'Risiken können vermieden werden, beispielsweise indem die Risikoursache ausgeschlossen wird, reduziert werden, indem die Rahmenbedingungen, die zur Risikoeinstufung beigetragen haben, modifiziert werden, transferiert werden, indem die Risiken mit anderen Parteien geteilt werden, z. B. durch Outsourcing oder Versicherungen, akzeptiert werden (auf Basis einer nachvollziehbaren Faktenlage), beispielsweise weil die mit dem Risiko einhergehenden Chancen wahrgenommen werden sollen.',
                'source' => 'BSI-Standard 200-1, Kapitel 8.1, S. 34',
                'answers' => [
                    ['text' => 'Risiken vermeiden (Risikoursache ausschließen)', 'is_correct' => true],
                    ['text' => 'Risiken reduzieren (Rahmenbedingungen modifizieren)', 'is_correct' => true],
                    ['text' => 'Risiken transferieren (z.B. durch Outsourcing oder Versicherungen)', 'is_correct' => true],
                    ['text' => 'Risiken akzeptieren (auf Basis einer nachvollziehbaren Faktenlage)', 'is_correct' => true],
                    ['text' => 'Risiken ignorieren und nicht dokumentieren', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was muss mit dem verbleibenden Restrisiko nach der Risikobehandlung geschehen?',
                'explanation' => 'Das verbleibende Restrisiko muss der Leitungsebene zur Zustimmung (Risiko-Akzeptanz) vorgelegt werden. Damit wird nachvollziehbar dokumentiert, dass die Institution sich des Restrisikos bewusst ist. Dies ist ein formaler Akt, der sicherstellt, dass die Leitungsebene die Verantwortung übernimmt.',
                'quote' => 'Das verbleibende Risiko muss anschließend der Leitungsebene zur Zustimmung („Risiko-Akzeptanz") vorgelegt werden. Damit wird nachvollziehbar dokumentiert, dass die Institution sich des Restrisikos bewusst ist.',
                'source' => 'BSI-Standard 200-1, Kapitel 8.1, S. 34',
                'answers' => [
                    ['text' => 'Es muss der Leitungsebene zur Risiko-Akzeptanz vorgelegt werden', 'is_correct' => true],
                    ['text' => 'Die Institution muss nachvollziehbar dokumentieren, dass sie sich des Restrisikos bewusst ist', 'is_correct' => true],
                    ['text' => 'Es kann vom ISB eigenständig akzeptiert werden', 'is_correct' => false],
                    ['text' => 'Es muss vollständig beseitigt werden, bevor das ISMS in Betrieb gehen kann', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Mit welchen qualitativen Kategorien empfiehlt der BSI-Standard 200-1, Risiken zu klassifizieren?',
                'explanation' => 'Es wird empfohlen, mit qualitativen Kategorien zu arbeiten. Für die Eintrittswahrscheinlichkeit werden die Kategorien selten, mittel, häufig und sehr häufig vorgeschlagen. Für die potenzielle Schadenshöhe: vernachlässigbar, begrenzt, beträchtlich und existenzbedrohend. Pro Dimension sollten nicht mehr als fünf Kategorien verwendet werden.',
                'quote' => 'Eintrittswahrscheinlichkeit: selten, mittel, häufig, sehr häufig. Potenzielle Schadenshöhe: vernachlässigbar, begrenzt, beträchtlich, existenzbedrohend',
                'source' => 'BSI-Standard 200-1, Kapitel 8.1, S. 33',
                'answers' => [
                    ['text' => 'Eintrittswahrscheinlichkeit: selten, mittel, häufig, sehr häufig', 'is_correct' => true],
                    ['text' => 'Schadenshöhe: vernachlässigbar, begrenzt, beträchtlich, existenzbedrohend', 'is_correct' => true],
                    ['text' => 'Eintrittswahrscheinlichkeit: 0-25%, 25-50%, 50-75%, 75-100%', 'is_correct' => false],
                    ['text' => 'Schadenshöhe wird ausschließlich in Euro beziffert', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Themen müssen laut BSI-Standard 200-1 im Realisierungsplan für das Sicherheitskonzept enthalten sein?',
                'explanation' => 'Der Realisierungsplan muss enthalten: Festlegung von Prioritäten (Umsetzungsreihenfolge), Festlegung von Verantwortlichkeiten für die Initiierung, Bereitstellung von Ressourcen durch das Management sowie eine Umsetzungsplanung einzelner Maßnahmen mit Terminen, Kosten und Verantwortlichkeiten.',
                'quote' => 'Ein Realisierungsplan muss folgende Themen enthalten: Festlegung von Prioritäten (Umsetzungsreihenfolge), Festlegung von Verantwortlichkeiten für Initiierung, Bereitstellung von Ressourcen durch das Management und Umsetzungsplanung einzelner Maßnahmen (Festlegung von Terminen und Kosten, Festlegung von Verantwortlichen für die Realisierung sowie von Verantwortlichen für die Kontrolle der Umsetzung bzw. der Effektivität von Maßnahmen).',
                'source' => 'BSI-Standard 200-1, Kapitel 8.2, S. 36',
                'answers' => [
                    ['text' => 'Festlegung von Prioritäten (Umsetzungsreihenfolge)', 'is_correct' => true],
                    ['text' => 'Festlegung von Verantwortlichkeiten und Bereitstellung von Ressourcen', 'is_correct' => true],
                    ['text' => 'Umsetzungsplanung mit Terminen, Kosten und Verantwortlichen', 'is_correct' => true],
                    ['text' => 'Vollständige Marktanalyse aller verfügbaren Sicherheitsprodukte', 'is_correct' => false],
                ],
            ],
            // === Kapitel 9: Zertifizierung ===
            [
                'text' => 'Welche Aussagen zur Zertifizierung eines ISMS nach ISO 27001 auf Basis von IT-Grundschutz sind laut BSI-Standard 200-1 korrekt?',
                'explanation' => 'Die Standard- und Kern-Absicherung des IT-Grundschutzes bilden die Anforderungen der ISO/IEC 27001 ab. Das BSI stellt das IT-Grundschutz-Kompendium als Prüfkatalog im Gegensatz zu anderen Zertifizierungsstellen kostenfrei zur Verfügung. Grundlage ist ein Audit durch einen externen, beim BSI zertifizierten Auditor.',
                'quote' => 'Die Standard- und die Kern-Absicherung des IT-Grundschutzes bilden die Anforderungen der ISO/IEC 27001 ab. [...] Das IT-Grundschutz-Kompendium bildet den Prüfkatalog für die Zertifizierung nach ISO/IEC 27001. [...] Das IT-Grundschutz-Kompendium als Prüfkatalog wird vom BSI (im Gegensatz zu anderen Zertifizierungsstellen) kostenfrei zur Verfügung gestellt.',
                'source' => 'BSI-Standard 200-1, Kapitel 9, S. 39',
                'answers' => [
                    ['text' => 'Standard- und Kern-Absicherung bilden die Anforderungen der ISO/IEC 27001 ab', 'is_correct' => true],
                    ['text' => 'Das IT-Grundschutz-Kompendium als Prüfkatalog wird vom BSI kostenfrei bereitgestellt', 'is_correct' => true],
                    ['text' => 'Jede Vorgehensweise des IT-Grundschutzes genügt automatisch für eine Zertifizierung', 'is_correct' => false],
                    ['text' => 'Die Zertifizierung kann nur durch das BSI selbst durchgeführt werden', 'is_correct' => false],
                ],
            ],
            // === Kapitel 10: ISMS auf Basis von IT-Grundschutz ===
            [
                'text' => 'Welche Schritte umfasst die Erstellung einer Sicherheitskonzeption nach IT-Grundschutz laut BSI-Standard 200-1?',
                'explanation' => 'Die Sicherheitskonzeption nach IT-Grundschutz umfasst: Definition des Informationsverbunds und Festlegung des Geltungsbereichs, Strukturanalyse (Identifikation von Schutzobjekten), Schutzbedarfsfeststellung, Modellierung (Auswahl der Sicherheitsanforderungen), IT-Grundschutz-Check (Soll-Ist-Vergleich), Risikoanalyse und Umsetzung der Maßnahmen.',
                'quote' => 'Für die Erstellung einer Sicherheitskonzeption nach IT-Grundschutz sind die folgenden Schritte zu durchlaufen: Definition des Informationsverbunds, Festlegung des Geltungsbereichs [...] Strukturanalyse: Identifikation von Schutzobjekten [...] Schutzbedarfsfeststellung [...] Modellierung: Auswahl der Sicherheitsanforderungen [...] IT-Grundschutz-Check: Durchführung eines Soll-Ist-Vergleichs [...] Risikoanalyse [...] Umsetzung der Maßnahmen',
                'source' => 'BSI-Standard 200-1, Kapitel 10.2.2, S. 43-46',
                'answers' => [
                    ['text' => 'Definition des Informationsverbunds und Strukturanalyse', 'is_correct' => true],
                    ['text' => 'Schutzbedarfsfeststellung und Modellierung', 'is_correct' => true],
                    ['text' => 'IT-Grundschutz-Check und Risikoanalyse', 'is_correct' => true],
                    ['text' => 'Beschaffung eines zertifizierten Sicherheitsprodukts', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was unterscheidet die integrierte Risikobewertung im IT-Grundschutz von einer klassischen Risikoanalyse?',
                'explanation' => 'Die IT-Grundschutz-Methodik beinhaltet bereits ein qualitatives Verfahren zur Risikobewertung, das die notwendigen Informationen zur Beurteilung von geschäftsschädigenden Sicherheitsvorfällen liefert. Im Vergleich zum quantitativen Verfahren ist es leichter im Umgang, da es für alle betrachteten Fälle ausreichend ist. Der eigene Arbeitsaufwand wird deutlich reduziert.',
                'quote' => 'In der IT-Grundschutz-Methodik ist daher bereits ein qualitatives Verfahren zur Risikobewertung enthalten, das die notwendigen Informationen zur Beurteilung von geschäftsschädigenden Sicherheitsvorfällen liefert und, im Vergleich zum quantitativen Verfahren, leichter im Umgang sowie für alle betrachteten Fälle ausreichend ist.',
                'source' => 'BSI-Standard 200-1, Kapitel 10.2.1, S. 41',
                'answers' => [
                    ['text' => 'Die IT-Grundschutz-Methodik enthält bereits ein qualitatives Verfahren zur Risikobewertung', 'is_correct' => true],
                    ['text' => 'Es ist leichter im Umgang als ein quantitatives Verfahren', 'is_correct' => true],
                    ['text' => 'Es erfordert eine vollständige quantitative Berechnung aller Schadenshöhen', 'is_correct' => false],
                    ['text' => 'Es ersetzt die Betrachtung von Bedrohungen vollständig', 'is_correct' => false],
                ],
            ],
        ];

        foreach ($questions as $questionData) {
            $answers = $questionData['answers'];
            unset($questionData['answers']);

            $question = Question::create([
                ...$questionData,
                'module_id' => $module->id,
            ]);

            foreach ($answers as $answerData) {
                Answer::create([
                    ...$answerData,
                    'question_id' => $question->id,
                ]);
            }
        }
    }
}
