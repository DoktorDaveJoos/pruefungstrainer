<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Database\Seeder;

class BsiStandard2003QuestionsSeeder extends Seeder
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
                'text' => 'Welchen BSI-Standard löst der BSI-Standard 200-3 ab?',
                'explanation' => 'Der BSI-Standard 200-3 löst den BSI-Standard 100-3 ab. Die Version 1.0 wurde im Oktober 2017 veröffentlicht.',
                'quote' => 'Der BSI-Standard 200-3 löst den BSI-Standard 100-3 ab.',
                'source' => 'BSI-Standard 200-3, Kapitel 1.1, S. 5',
                'answers' => [
                    ['text' => 'BSI-Standard 100-3', 'is_correct' => true],
                    ['text' => 'BSI-Standard 100-1', 'is_correct' => false],
                    ['text' => 'BSI-Standard 200-2', 'is_correct' => false],
                    ['text' => 'BSI-Standard 100-4', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Zielsetzung verfolgt der BSI-Standard 200-3?',
                'explanation' => 'Der Standard stellt ein leicht anzuwendendes und anerkanntes Vorgehen zur Verfügung, mit dem Institutionen ihre Informationssicherheitsrisiken angemessen und zielgerichtet steuern können. Er basiert auf den elementaren Gefährdungen des IT-Grundschutz-Kompendiums.',
                'quote' => 'Mit dem BSI-Standard 200-3 stellt das BSI ein leicht anzuwendendes und anerkanntes Vorgehen zur Verfügung, mit dem Institutionen ihre Informationssicherheitsrisiken angemessen und zielgerichtet steuern können.',
                'source' => 'BSI-Standard 200-3, Kapitel 1.2, S. 5',
                'answers' => [
                    ['text' => 'Ein leicht anzuwendendes Vorgehen zur Steuerung von Informationssicherheitsrisiken bereitzustellen', 'is_correct' => true],
                    ['text' => 'Eine vollständige quantitative Risikobewertung aller IT-Systeme durchzuführen', 'is_correct' => false],
                    ['text' => 'Die technische Konfiguration von Firewalls und Servern festzulegen', 'is_correct' => false],
                    ['text' => 'Einen Ersatz für die ISO/IEC 27001-Zertifizierung zu bieten', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'In welchen Fällen muss laut BSI-Standard 200-3 explizit eine Risikoanalyse durchgeführt werden?',
                'explanation' => 'Eine explizite Risikoanalyse ist erforderlich, wenn Zielobjekte einen hohen oder sehr hohen Schutzbedarf haben, mit existierenden Bausteinen nicht hinreichend modelliert werden können, oder in Einsatzszenarien betrieben werden, die im IT-Grundschutz nicht vorgesehen sind.',
                'quote' => 'In bestimmten Fällen muss jedoch explizit eine Risikoanalyse durchgeführt werden, beispielsweise wenn der betrachtete Informationsverbund Zielobjekte enthält, die einen hohen oder sehr hohen Schutzbedarf in mindestens einem der drei Grundwerte Vertraulichkeit, Integrität oder Verfügbarkeit haben oder mit den existierenden Bausteinen des IT-Grundschutzes nicht hinreichend abgebildet (modelliert) werden können oder in Einsatzszenarien (Umgebung, Anwendung) betrieben werden, die im Rahmen des IT-Grundschutzes nicht vorgesehen sind.',
                'source' => 'BSI-Standard 200-3, Kapitel 1.3, S. 6',
                'answers' => [
                    ['text' => 'Bei hohem oder sehr hohem Schutzbedarf in mindestens einem Grundwert', 'is_correct' => true],
                    ['text' => 'Wenn Zielobjekte mit existierenden IT-Grundschutz-Bausteinen nicht hinreichend modelliert werden können', 'is_correct' => true],
                    ['text' => 'Bei Einsatzszenarien, die im IT-Grundschutz nicht vorgesehen sind', 'is_correct' => true],
                    ['text' => 'Bei jedem Zielobjekt mit normalem Schutzbedarf', 'is_correct' => false],
                    ['text' => 'Nur wenn eine ISO 27001-Zertifizierung angestrebt wird', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Wie ist die Risikoanalyse im BSI-Standard 200-3 angelegt?',
                'explanation' => 'Die Risikoanalyse ist zweistufig: Zunächst wird die Gefährdungsübersicht systematisch abgearbeitet und bewertet unter der Annahme, dass Basis- und Standard-Sicherheitsmaßnahmen bereits umgesetzt sind. Dann schließt sich eine erneute Bewertung an, bei der zusätzliche Maßnahmen zur Risikobehandlung betrachtet werden.',
                'quote' => 'Im vorliegenden BSI-Standard 200-3 ist die Risikoanalyse zweistufig angelegt. In einem ersten Schritt wird die in Kapitel 4 erstellte Gefährdungsübersicht systematisch abgearbeitet. Dabei wird für jedes Zielobjekt und jede Gefährdung eine Bewertung unter der Annahme vorgenommen, dass bereits Sicherheitsmaßnahmen umgesetzt oder geplant worden sind.',
                'source' => 'BSI-Standard 200-3, Kapitel 1.3, S. 6',
                'answers' => [
                    ['text' => 'Zweistufig: erst Bewertung mit bestehenden Maßnahmen, dann erneute Bewertung mit zusätzlichen Maßnahmen', 'is_correct' => true],
                    ['text' => 'Einstufig mit einer einmaligen quantitativen Bewertung', 'is_correct' => false],
                    ['text' => 'Dreistufig: Identifikation, Analyse, Transfer', 'is_correct' => false],
                    ['text' => 'Iterativ ohne festgelegte Reihenfolge der Schritte', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Schritte sieht die Risikoanalyse nach BSI-Standard 200-3 vor?',
                'explanation' => 'Die Risikoanalyse umfasst vier Schritte: 1) Erstellung einer Gefährdungsübersicht (Kapitel 4), 2) Risikoeinstufung bestehend aus Risikoeinschätzung und Risikobewertung (Kapitel 5), 3) Risikobehandlung (Kapitel 6), und 4) Konsolidierung des Sicherheitskonzepts (Kapitel 7).',
                'quote' => 'Schritt 1: Erstellung einer Gefährdungsübersicht (siehe Kapitel 4) [...] Schritt 2: Risikoeinstufung (siehe Kapitel 5) [...] Schritt 3: Risikobehandlung (siehe Kapitel 6) [...] Schritt 4: Konsolidierung des Sicherheitskonzepts (siehe Kapitel 7)',
                'source' => 'BSI-Standard 200-3, Kapitel 1.3, S. 7',
                'answers' => [
                    ['text' => 'Erstellung einer Gefährdungsübersicht', 'is_correct' => true],
                    ['text' => 'Risikoeinstufung (Risikoeinschätzung und Risikobewertung)', 'is_correct' => true],
                    ['text' => 'Risikobehandlung', 'is_correct' => true],
                    ['text' => 'Konsolidierung des Sicherheitskonzepts', 'is_correct' => true],
                    ['text' => 'Durchführung eines Penetrationstests', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Risikobehandlungsoptionen werden im Schritt "Risikobehandlung" des BSI-Standard 200-3 genannt?',
                'explanation' => 'Im Rahmen der Risikobehandlung werden vier Optionen genannt: Risikovermeidung, Risikoreduktion (durch Sicherheitsmaßnahmen), Risikotransfer und Risikoakzeptanz.',
                'quote' => 'Schritt 3: Risikobehandlung (siehe Kapitel 6) Risikovermeidung Risikoreduktion (Ermittlung von Sicherheitsmaßnahmen) Risikotransfer Risikoakzeptanz',
                'source' => 'BSI-Standard 200-3, Kapitel 1.3, S. 7',
                'answers' => [
                    ['text' => 'Risikovermeidung', 'is_correct' => true],
                    ['text' => 'Risikoreduktion (Ermittlung von Sicherheitsmaßnahmen)', 'is_correct' => true],
                    ['text' => 'Risikotransfer', 'is_correct' => true],
                    ['text' => 'Risikoakzeptanz', 'is_correct' => true],
                    ['text' => 'Risikoignoranz', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was versteht der BSI-Standard 200-3 unter dem Begriff "Risikoanalyse"?',
                'explanation' => 'Im BSI-Standard 200-3 wird "Risikoanalyse" als der komplette Prozess bezeichnet, um Risiken zu beurteilen (identifizieren, einschätzen und bewerten) sowie zu behandeln. Dies weicht von der ISO-Terminologie ab, wo "Risikoanalyse" nur einen Teilschritt der Risikobeurteilung darstellt.',
                'quote' => 'Als Risikoanalyse wird in diesem Werk der komplette Prozess bezeichnet, um Risiken zu beurteilen (identifizieren, einschätzen und bewerten) sowie zu behandeln. Risikoanalyse bezeichnet aber nach den einschlägigen ISO-Normen ISO 31000 (siehe [31000]) und ISO 27005 (siehe [27005]) nur einen Schritt im Rahmen der Risikobeurteilung',
                'source' => 'BSI-Standard 200-3, Kapitel 1.3, S. 6-7',
                'answers' => [
                    ['text' => 'Den kompletten Prozess, um Risiken zu beurteilen und zu behandeln', 'is_correct' => true],
                    ['text' => 'Nur die quantitative Berechnung von Eintrittswahrscheinlichkeiten', 'is_correct' => false],
                    ['text' => 'Ausschließlich die Identifikation von Gefährdungen ohne Bewertung', 'is_correct' => false],
                    ['text' => 'Die technische Prüfung von IT-Systemen auf Schwachstellen', 'is_correct' => false],
                ],
            ],
            // === Kapitel 2: Vorarbeiten zur Risikoanalyse ===
            [
                'text' => 'Welche Vorarbeiten müssen gemäß BSI-Standard 200-3 abgeschlossen sein, bevor die Risikoanalyse beginnt?',
                'explanation' => 'Vor der Risikoanalyse müssen mehrere Vorarbeiten aus dem BSI-Standard 200-2 abgeschlossen sein: ein systematischer IS-Prozess muss initiiert, ein Geltungsbereich (Informationsverbund) definiert, eine Strukturanalyse und Schutzbedarfsfeststellung durchgeführt, eine Modellierung erstellt und ein IT-Grundschutz-Check durchgeführt worden sein.',
                'quote' => 'Bevor die eigentliche Risikoanalyse beginnt, sollten folgende Vorarbeiten abgeschlossen sein, die in der IT-Grundschutz-Methodik gemäß BSI-Standard 200-2 beschrieben sind: Es muss ein systematischer Informationssicherheitsprozess initiiert worden sein. [...] ein Geltungsbereich für die Sicherheitskonzeption definiert worden sein. [...] eine Strukturanalyse [...] eine Schutzbedarfsfeststellung [...] eine Modellierung [...] ein IT-Grundschutz-Check',
                'source' => 'BSI-Standard 200-3, Kapitel 2, S. 9',
                'answers' => [
                    ['text' => 'Initiierung eines systematischen IS-Prozesses', 'is_correct' => true],
                    ['text' => 'Definition des Geltungsbereichs (Informationsverbund)', 'is_correct' => true],
                    ['text' => 'Strukturanalyse und Schutzbedarfsfeststellung', 'is_correct' => true],
                    ['text' => 'Modellierung und IT-Grundschutz-Check', 'is_correct' => true],
                    ['text' => 'Abschluss einer externen ISO 27001-Zertifizierung', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was muss die Richtlinie zur Risikoanalyse laut BSI-Standard 200-3 unter anderem regeln?',
                'explanation' => 'Die Richtlinie muss unter anderem festlegen: unter welchen Voraussetzungen eine Risikoanalyse durchzuführen ist, welche Methodik verwendet wird, was die Risikoakzeptanzkriterien sind, welche Organisationseinheiten verantwortlich sind, und in welchem Zeitrahmen die Risikoanalyse vollständig aktualisiert werden muss.',
                'quote' => 'Unter welchen Voraussetzungen muss in jedem Fall eine Risikoanalyse durchgeführt werden? Welche Methodik beziehungsweise welcher Standard wird dazu eingesetzt, um die Risiken zu identifizieren, einzuschätzen, zu bewerten und zu behandeln? [...] Was sind die Risikoakzeptanzkriterien? [...] In welchem Zeitrahmen muss die Risikoanalyse vollständig aktualisiert werden?',
                'source' => 'BSI-Standard 200-3, Kapitel 2, S. 10',
                'answers' => [
                    ['text' => 'Voraussetzungen für die Durchführung einer Risikoanalyse', 'is_correct' => true],
                    ['text' => 'Risikoakzeptanzkriterien', 'is_correct' => true],
                    ['text' => 'Zeitrahmen für die vollständige Aktualisierung', 'is_correct' => true],
                    ['text' => 'Die konkreten IP-Adressen aller zu schützenden Server', 'is_correct' => false],
                    ['text' => 'Die Gehälter der IT-Sicherheitsmitarbeiter', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussage zur Priorisierung von Zielobjekten bei der Risikoanalyse ist NICHT korrekt?',
                'explanation' => 'Bei der Vorgehensweise "Standard-Absicherung" sollen vorrangig die übergeordneten Zielobjekte bearbeitet werden, bei "Kern-Absicherung" die mit dem höchsten Schutzbedarf. Bei "Basis-Absicherung" werden zunächst keine Risikoanalysen durchgeführt.',
                'quote' => 'Falls für den IT-Grundschutz die Vorgehensweise „Standard-Absicherung" gewählt wurde, sollten vorrangig die übergeordneten Zielobjekte bearbeitet werden [...] Falls für den IT-Grundschutz die Vorgehensweise „Kern-Absicherung" gewählt wurde, sollten vorrangig die Zielobjekte mit dem höchsten Schutzbedarf bearbeitet werden. Falls für den IT-Grundschutz die Vorgehensweise „Basis-Absicherung" gewählt wurde, werden zunächst keine Risikoanalysen durchgeführt',
                'source' => 'BSI-Standard 200-3, Kapitel 2, S. 9-10',
                'answers' => [
                    ['text' => 'Bei der Basis-Absicherung werden von Anfang an umfassende Risikoanalysen für alle Zielobjekte durchgeführt', 'is_correct' => true],
                    ['text' => 'Bei der Kern-Absicherung werden vorrangig Zielobjekte mit dem höchsten Schutzbedarf bearbeitet', 'is_correct' => false],
                    ['text' => 'Bei der Standard-Absicherung werden vorrangig übergeordnete Zielobjekte bearbeitet', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussage zu Zielobjekten der Risikoanalyse trifft zu?',
                'explanation' => 'Die Risikoanalyse muss sich nicht zwangsläufig auf systemorientierte Zielobjekte wie Anwendungen, IT-Systeme oder Räume beschränken. Sie kann vielmehr auch auf Geschäftsprozessebene durchgeführt werden.',
                'quote' => 'Bei den betrachteten Zielobjekten muss es sich nicht zwangsläufig um systemorientierte Zielobjekte (z. B. Anwendungen, IT-Systeme oder -Räume) handeln. Vielmehr kann die Risikoanalyse auch auf Geschäftsprozessebene durchgeführt werden.',
                'source' => 'BSI-Standard 200-3, Kapitel 2, S. 10',
                'answers' => [
                    ['text' => 'Die Risikoanalyse kann auch auf Geschäftsprozessebene durchgeführt werden', 'is_correct' => true],
                    ['text' => 'Zielobjekte müssen immer einzelne IT-Systeme sein', 'is_correct' => false],
                    ['text' => 'Nur physische Räume können als Zielobjekte betrachtet werden', 'is_correct' => false],
                    ['text' => 'Geschäftsprozesse dürfen erst nach einer separaten Genehmigung als Zielobjekte betrachtet werden', 'is_correct' => false],
                ],
            ],
            // === Kapitel 3: Übersicht über die elementaren Gefährdungen ===
            [
                'text' => 'Wie viele elementare Gefährdungen hat das BSI im IT-Grundschutz-Kompendium zusammengefasst?',
                'explanation' => 'Das BSI hat die vielen spezifischen Einzelgefährdungen der IT-Grundschutz-Bausteine in 47 elementare Gefährdungen überführt, die im IT-Grundschutz-Kompendium aufgelistet sind.',
                'quote' => 'Das BSI hat aus den vielen spezifischen Einzelgefährdungen der IT-Grundschutz-Bausteine die generellen Aspekte herausgearbeitet und in 47 elementare Gefährdungen überführt, die im IT-Grundschutz-Kompendium aufgelistet sind.',
                'source' => 'BSI-Standard 200-3, Kapitel 3, S. 13',
                'answers' => [
                    ['text' => '47', 'is_correct' => true],
                    ['text' => '27', 'is_correct' => false],
                    ['text' => '100', 'is_correct' => false],
                    ['text' => '35', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Eigenschaften zeichnen die elementaren Gefährdungen im IT-Grundschutz aus?',
                'explanation' => 'Die elementaren Gefährdungen wurden für die Risikoanalyse optimiert, sind produktneutral (immer) und technikneutral (möglichst), kompatibel mit internationalen Katalogen und Standards und nahtlos in den IT-Grundschutz integriert.',
                'quote' => 'Elementare Gefährdungen sind für die Verwendung bei der Risikoanalyse optimiert, produktneutral (immer), technikneutral (möglichst, bestimmte Techniken prägen so stark den Markt, dass sie auch die abstrahierten Gefährdungen beeinflussen), kompatibel mit vergleichbaren internationalen Katalogen und Standards und nahtlos in den IT-Grundschutz integriert.',
                'source' => 'BSI-Standard 200-3, Kapitel 3, S. 13',
                'answers' => [
                    ['text' => 'Für die Verwendung bei der Risikoanalyse optimiert', 'is_correct' => true],
                    ['text' => 'Produktneutral und möglichst technikneutral', 'is_correct' => true],
                    ['text' => 'Kompatibel mit internationalen Katalogen und Standards', 'is_correct' => true],
                    ['text' => 'Spezifisch auf einzelne Herstellerprodukte zugeschnitten', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welchem Grundwert ist die elementare Gefährdung G 0.1 Feuer laut BSI-Standard 200-3 zugeordnet?',
                'explanation' => 'G 0.1 Feuer wird als einziger betroffener Grundwert "Verfügbarkeit" (A) zugeordnet. Der Standard erklärt, dass ein Feuer natürlich auch die Integrität von Datenträgern verletzen könnte, aber nur die Verfügbarkeit unmittelbar beeinträchtigt wäre.',
                'quote' => 'So wird z. B. zu G 0.1 Feuer als einziger betroffener Grundwert „Verfügbarkeit" genannt. Natürlich könnte ein Feuer einen Datenträger auch so beschädigen, dass die abgespeicherten Informationen zwar noch vorhanden wären, aber deren Integrität verletzt wäre.',
                'source' => 'BSI-Standard 200-3, Kapitel 3, S. 13',
                'answers' => [
                    ['text' => 'Verfügbarkeit (A)', 'is_correct' => true],
                    ['text' => 'Vertraulichkeit (C)', 'is_correct' => false],
                    ['text' => 'Integrität (I)', 'is_correct' => false],
                    ['text' => 'Vertraulichkeit, Integrität und Verfügbarkeit (C, I, A)', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Wofür stehen die Abkürzungen C, I und A in der Gefährdungsübersicht des BSI-Standard 200-3?',
                'explanation' => 'In der Übersicht über die elementaren Gefährdungen steht C für Confidentiality (Vertraulichkeit), I für Integrity (Integrität) und A für Availability (Verfügbarkeit). Diese bezeichnen die hauptsächlich betroffenen Grundwerte je Gefährdung.',
                'quote' => 'Dabei steht C für Confidentiality (Vertraulichkeit), I für Integrity (Integrität) und A für Availability (Verfügbarkeit).',
                'source' => 'BSI-Standard 200-3, Kapitel 3, S. 13',
                'answers' => [
                    ['text' => 'C = Confidentiality (Vertraulichkeit), I = Integrity (Integrität), A = Availability (Verfügbarkeit)', 'is_correct' => true],
                    ['text' => 'C = Compliance, I = Information, A = Audit', 'is_correct' => false],
                    ['text' => 'C = Control, I = Implementation, A = Assessment', 'is_correct' => false],
                ],
            ],
            // === Kapitel 4: Erstellung einer Gefährdungsübersicht ===
            [
                'text' => 'Was bedeutet "direkt relevant" bei der Ermittlung elementarer Gefährdungen?',
                'explanation' => 'Eine Gefährdung ist "direkt relevant", wenn sie auf das betrachtete Zielobjekt einwirken kann und deshalb im Rahmen der Risikoanalyse behandelt werden muss.',
                'quote' => '„Direkt relevant" bedeutet hier, dass die jeweilige Gefährdung auf das betrachtete Zielobjekt einwirken kann und deshalb im Rahmen der Risikoanalyse behandelt werden muss.',
                'source' => 'BSI-Standard 200-3, Kapitel 4.1, S. 16',
                'answers' => [
                    ['text' => 'Die Gefährdung kann auf das Zielobjekt einwirken und muss in der Risikoanalyse behandelt werden', 'is_correct' => true],
                    ['text' => 'Die Gefährdung hat nur indirekte Auswirkungen und kann ignoriert werden', 'is_correct' => false],
                    ['text' => 'Die Gefährdung betrifft nur benachbarte Zielobjekte', 'is_correct' => false],
                    ['text' => 'Die Gefährdung wurde bereits vollständig durch Maßnahmen abgedeckt', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was unterscheidet die Relevanzstufen "direkt relevant", "indirekt relevant" und "nicht relevant" bei der Gefährdungsermittlung?',
                'explanation' => 'Eine direkt relevante Gefährdung wirkt auf das Zielobjekt und muss behandelt werden. Eine indirekt relevante Gefährdung wirkt zwar auf das Zielobjekt, aber nicht über allgemeinere Gefährdungen hinaus — sie wird nicht gesondert behandelt. Eine nicht relevante Gefährdung kann nicht auf das Zielobjekt einwirken.',
                'quote' => '„Direkt relevant" bedeutet hier, dass die jeweilige Gefährdung auf das betrachtete Zielobjekt einwirken kann und deshalb im Rahmen der Risikoanalyse behandelt werden muss. „Indirekt relevant" meint hier, dass die jeweilige Gefährdung zwar auf das betrachtete Zielobjekt einwirken kann, in ihrer potenziellen Wirkung aber nicht über andere (allgemeinere) Gefährdungen hinausgeht. „Nicht relevant" heißt hier, dass die jeweilige Gefährdung nicht auf das betrachtete Zielobjekt einwirken kann',
                'source' => 'BSI-Standard 200-3, Kapitel 4.1, S. 16',
                'answers' => [
                    ['text' => 'Direkt relevant: wirkt auf das Zielobjekt und muss behandelt werden', 'is_correct' => true],
                    ['text' => 'Indirekt relevant: wirkt auf das Zielobjekt, geht aber nicht über allgemeinere Gefährdungen hinaus', 'is_correct' => true],
                    ['text' => 'Nicht relevant: kann nicht auf das Zielobjekt einwirken', 'is_correct' => true],
                    ['text' => 'Indirekt relevant: muss als eigenständiges Risiko gesondert behandelt werden', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Wann wird bei der Ermittlung elementarer Gefährdungen die vollständige Liste der 47 Gefährdungen herangezogen?',
                'explanation' => 'Die vollständige Liste wird herangezogen, wenn das betrachtete Zielobjekt nicht hinreichend mit bestehenden Bausteinen des IT-Grundschutz-Kompendiums abgebildet werden kann, d. h. wenn es sich um Themenbereiche handelt, die im Kompendium noch nicht oder nicht ausreichend abgedeckt sind.',
                'quote' => 'Kann das betrachtete Zielobjekt nicht hinreichend mit bestehenden Bausteinen des IT-Grundschutz-Kompendiums abgebildet werden, da es sich um Themenbereiche handelt, die bisher im IT-Grundschutz-Kompendium noch nicht oder nicht ausreichend abgedeckt sind, um den betrachteten Informationsverbund modellieren zu können, dann wird die Liste der 47 elementaren Gefährdungen herangezogen',
                'source' => 'BSI-Standard 200-3, Kapitel 4.1, S. 17',
                'answers' => [
                    ['text' => 'Wenn das Zielobjekt nicht hinreichend mit bestehenden IT-Grundschutz-Bausteinen abgebildet werden kann', 'is_correct' => true],
                    ['text' => 'Immer, bei jedem Zielobjekt unabhängig von der Modellierung', 'is_correct' => false],
                    ['text' => 'Nur bei Zielobjekten mit normalem Schutzbedarf', 'is_correct' => false],
                    ['text' => 'Ausschließlich bei der Vorgehensweise Basis-Absicherung', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Kriterien sind bei der Ermittlung zusätzlicher Gefährdungen zu beachten?',
                'explanation' => 'Relevante Gefährdungen für die Informationssicherheit müssen zwei Kriterien erfüllen: Sie müssen zu einem nennenswerten Schaden führen können und im vorliegenden Anwendungsfall und Einsatzumfeld realistisch sein.',
                'quote' => 'Für die Informationssicherheit relevante Gefährdungen sind solche, die zu einem nennenswerten Schaden führen können und im vorliegenden Anwendungsfall und Einsatzumfeld realistisch sind.',
                'source' => 'BSI-Standard 200-3, Kapitel 4.2, S. 23',
                'answers' => [
                    ['text' => 'Sie müssen zu einem nennenswerten Schaden führen können', 'is_correct' => true],
                    ['text' => 'Sie müssen im vorliegenden Anwendungsfall und Einsatzumfeld realistisch sein', 'is_correct' => true],
                    ['text' => 'Sie müssen bereits in einer internationalen Norm dokumentiert sein', 'is_correct' => false],
                    ['text' => 'Sie müssen in der Vergangenheit bereits einmal eingetreten sein', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Wie sollte bei der Identifikation zusätzlicher Gefährdungen vorgegangen werden?',
                'explanation' => 'In der Praxis hat sich ein gemeinsames Brainstorming mit allen beteiligten Mitarbeitern bewährt. Informationssicherheitsbeauftragte, Fachverantwortliche, Administratoren und Benutzer des jeweiligen Zielobjekts und ggf. externe Sachverständige sollten beteiligt werden. Ein Experte für Informationssicherheit sollte das Brainstorming moderieren.',
                'quote' => 'In der Praxis hat es sich bewährt, zur Identifikation zusätzlicher Gefährdungen ein gemeinsames Brainstorming mit allen beteiligten Mitarbeitern durchzuführen. Es sollten Informationssicherheitsbeauftragte, Fachverantwortliche, Administratoren und Benutzer des jeweils betrachteten Zielobjekts und gegebenenfalls auch externe Sachverständige beteiligt werden. [...] Ein Experte für Informationssicherheit sollte das Brainstorming moderieren.',
                'source' => 'BSI-Standard 200-3, Kapitel 4.2, S. 24',
                'answers' => [
                    ['text' => 'Durch ein gemeinsames Brainstorming mit allen beteiligten Mitarbeitern', 'is_correct' => true],
                    ['text' => 'Unter Beteiligung von Informationssicherheitsbeauftragten, Fachverantwortlichen und Administratoren', 'is_correct' => true],
                    ['text' => 'Moderiert durch einen Experten für Informationssicherheit', 'is_correct' => true],
                    ['text' => 'Ausschließlich durch die Geschäftsleitung ohne Einbeziehung technischer Mitarbeiter', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welcher Grundwert sollte bei der Ermittlung zusätzlicher Gefährdungen vorrangig betrachtet werden, wenn ein Zielobjekt den Schutzbedarf "sehr hoch" in einem bestimmten Grundwert hat?',
                'explanation' => 'Bei der Ermittlung zusätzlicher relevanter Gefährdungen sollte der Schutzbedarf des Zielobjekts berücksichtigt werden: Hat es den Schutzbedarf "sehr hoch" in einem Grundwert, sollten vorrangig Gefährdungen gesucht werden, die diesen Grundwert beeinträchtigen.',
                'quote' => 'Hat das Zielobjekt in einem bestimmten Grundwert den Schutzbedarf sehr hoch, sollten vorrangig solche Gefährdungen gesucht werden, die diesen Grundwert beeinträchtigen.',
                'source' => 'BSI-Standard 200-3, Kapitel 4.2, S. 23',
                'answers' => [
                    ['text' => 'Der Grundwert, in dem der Schutzbedarf "sehr hoch" ist', 'is_correct' => true],
                    ['text' => 'Immer zuerst die Verfügbarkeit, unabhängig vom Schutzbedarf', 'is_correct' => false],
                    ['text' => 'Alle drei Grundwerte gleichzeitig und gleichwertig', 'is_correct' => false],
                    ['text' => 'Der Grundwert mit dem niedrigsten Schutzbedarf zuerst', 'is_correct' => false],
                ],
            ],
            // === Kapitel 5: Risikoeinstufung ===
            [
                'text' => 'Aus welchen zwei Einflussgrößen ergibt sich die Höhe eines Risikos bei der Risikoeinschätzung?',
                'explanation' => 'Die Höhe eines Risikos hängt sowohl von der Eintrittshäufigkeit (Eintrittseinschätzung) der Gefährdung als auch von der Höhe des Schadens ab, der dabei droht. Beide Einflussgrößen müssen bei der Risikoeinschätzung berücksichtigt werden.',
                'quote' => 'Wie hoch dieses Risiko ist, hängt sowohl von der Eintrittshäufigkeit (Eintrittseinschätzung) der Gefährdung als auch von der Höhe des Schadens ab, der dabei droht. Bei der Risikoeinschätzung müssen daher beide Einflussgrößen berücksichtigt werden.',
                'source' => 'BSI-Standard 200-3, Kapitel 5.1, S. 26',
                'answers' => [
                    ['text' => 'Eintrittshäufigkeit der Gefährdung und Höhe des Schadens', 'is_correct' => true],
                    ['text' => 'Anzahl der betroffenen Mitarbeiter und Dauer der Störung', 'is_correct' => false],
                    ['text' => 'Kosten der Sicherheitsmaßnahmen und verfügbares Budget', 'is_correct' => false],
                    ['text' => 'Komplexität des IT-Systems und Alter der eingesetzten Software', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Kategorien für die Eintrittshäufigkeit definiert der IT-Grundschutz?',
                'explanation' => 'Der IT-Grundschutz definiert vier Kategorien: selten (höchstens alle fünf Jahre), mittel (einmal alle fünf Jahre bis einmal im Jahr), häufig (einmal im Jahr bis einmal pro Monat) und sehr häufig (mehrmals im Monat).',
                'quote' => 'Eintrittshäufigkeit: selten, mittel, häufig, sehr häufig',
                'source' => 'BSI-Standard 200-3, Kapitel 5.1, S. 26',
                'answers' => [
                    ['text' => 'selten, mittel, häufig, sehr häufig', 'is_correct' => true],
                    ['text' => 'niedrig, mittel, hoch, kritisch', 'is_correct' => false],
                    ['text' => 'unwahrscheinlich, möglich, wahrscheinlich, sicher', 'is_correct' => false],
                    ['text' => 'gering, normal, erhöht, extrem', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Kategorien für die potenzielle Schadenshöhe definiert der IT-Grundschutz?',
                'explanation' => 'Der IT-Grundschutz definiert vier Kategorien: vernachlässigbar (gering, können vernachlässigt werden), begrenzt (begrenzt und überschaubar), beträchtlich (können beträchtlich sein) und existenzbedrohend (können ein existenziell bedrohliches, katastrophales Ausmaß erreichen).',
                'quote' => 'Potenzielle Schadenshöhe: vernachlässigbar, begrenzt, beträchtlich, existenzbedrohend',
                'source' => 'BSI-Standard 200-3, Kapitel 5.1, S. 26',
                'answers' => [
                    ['text' => 'vernachlässigbar, begrenzt, beträchtlich, existenzbedrohend', 'is_correct' => true],
                    ['text' => 'gering, mittel, hoch, sehr hoch', 'is_correct' => false],
                    ['text' => 'niedrig, normal, erhöht, kritisch', 'is_correct' => false],
                    ['text' => 'minimal, moderat, signifikant, katastrophal', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was beschreibt die Eintrittshäufigkeit "selten" im Kontext des BSI-Standard 200-3?',
                'explanation' => 'Die Kategorie "selten" bedeutet, dass ein Ereignis nach heutigem Kenntnisstand höchstens alle fünf Jahre eintreten könnte.',
                'quote' => 'selten: Ereignis könnte nach heutigem Kenntnisstand höchstens alle fünf Jahre eintreten.',
                'source' => 'BSI-Standard 200-3, Kapitel 5.1, S. 26',
                'answers' => [
                    ['text' => 'Ein Ereignis könnte höchstens alle fünf Jahre eintreten', 'is_correct' => true],
                    ['text' => 'Ein Ereignis tritt höchstens einmal im Jahr ein', 'is_correct' => false],
                    ['text' => 'Ein Ereignis ist praktisch ausgeschlossen', 'is_correct' => false],
                    ['text' => 'Ein Ereignis könnte höchstens alle zehn Jahre eintreten', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Risikokategorien ergeben sich aus der Risikobewertungsmatrix des BSI-Standard 200-3?',
                'explanation' => 'Die Risikobewertungsmatrix definiert vier Risikokategorien: gering (ausreichender Schutz durch bestehende Maßnahmen), mittel (Maßnahmen reichen möglicherweise nicht aus), hoch (kein ausreichender Schutz durch bestehende Maßnahmen) und sehr hoch (kein ausreichender Schutz, werden selten akzeptiert).',
                'quote' => 'gering: Die bereits umgesetzten oder zumindest im Sicherheitskonzept vorgesehenen Sicherheitsmaßnahmen bieten einen ausreichenden Schutz. [...] mittel: Die bereits umgesetzten [...] Sicherheitsmaßnahmen reichen möglicherweise nicht aus. [...] hoch: Die bereits umgesetzten [...] Sicherheitsmaßnahmen bieten keinen ausreichenden Schutz [...] sehr hoch: Die bereits umgesetzten [...] Sicherheitsmaßnahmen bieten keinen ausreichenden Schutz [...] In der Praxis werden sehr hohe Risiken selten akzeptiert.',
                'source' => 'BSI-Standard 200-3, Kapitel 5.2, S. 28',
                'answers' => [
                    ['text' => 'gering, mittel, hoch, sehr hoch', 'is_correct' => true],
                    ['text' => 'niedrig, normal, erhöht, kritisch', 'is_correct' => false],
                    ['text' => 'grün, gelb, orange, rot', 'is_correct' => false],
                    ['text' => 'akzeptabel, bedingt akzeptabel, inakzeptabel', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Risikokategorie ergibt sich laut der BSI-Risikomatrix bei einer Eintrittshäufigkeit "häufig" und einer Schadenshöhe "beträchtlich"?',
                'explanation' => 'Laut der Risikobewertungsmatrix (Abbildung 3) ergibt die Kombination aus häufiger Eintrittshäufigkeit und beträchtlicher Schadenshöhe ein "hohes" Risiko. Bei "selten" und "beträchtlich" wäre es "gering", bei "mittel" und "beträchtlich" wäre es "mittel".',
                'quote' => 'Anhand der zuvor definierten Kategorien für die potenzielle Schadenshöhe sowie der Klassifikation für Eintrittshäufigkeiten von Gefährdungen legt das BSI folgende Risikomatrix (siehe Abbildung 3) fest.',
                'source' => 'BSI-Standard 200-3, Kapitel 5.2, S. 27',
                'answers' => [
                    ['text' => 'hoch', 'is_correct' => true],
                    ['text' => 'mittel', 'is_correct' => false],
                    ['text' => 'sehr hoch', 'is_correct' => false],
                    ['text' => 'gering', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Warum empfiehlt der BSI-Standard 200-3, qualitative statt quantitative Kategorien für die Risikoeinschätzung zu verwenden?',
                'explanation' => 'Quantitative Risikobetrachtung erfordert umfangreiches statistisches Datenmaterial, das im dynamischen Umfeld der Informationssicherheit oft fehlt. Zudem ist die Interpretation statistischer Ergebnisse prinzipiell mit Unsicherheiten behaftet. Qualitative Kategorien sind daher in den meisten Fällen praktikabler.',
                'quote' => 'Die quantitative Risikobetrachtung ist sehr aufwändig und setzt umfangreiches statistisches Datenmaterial voraus. Solche umfangreichen Erfahrungswerte fehlen in den meisten Fällen im sehr dynamischen Umfeld der Informationssicherheit. Daher ist es in den meisten Fällen praktikabler, sowohl für die Eintrittshäufigkeit als auch für die potenzielle Schadenshöhe mit qualitativen Kategorien zu arbeiten.',
                'source' => 'BSI-Standard 200-3, Kapitel 5.1, S. 26',
                'answers' => [
                    ['text' => 'Umfangreiche statistische Erfahrungswerte fehlen in den meisten Fällen', 'is_correct' => true],
                    ['text' => 'Das dynamische Umfeld der Informationssicherheit erschwert quantitative Betrachtungen', 'is_correct' => true],
                    ['text' => 'Qualitative Kategorien sind quantitativen in jedem Fall überlegen', 'is_correct' => false],
                    ['text' => 'Quantitative Methoden sind durch die ISO 27001 verboten', 'is_correct' => false],
                ],
            ],
            // === Kapitel 6: Behandlung von Risiken ===
            [
                'text' => 'Wie können Risiken laut BSI-Standard 200-3 grundsätzlich behandelt werden?',
                'explanation' => 'Risiken können auf vier Arten behandelt werden: vermieden (Risikoursache ausschließen), reduziert (Rahmenbedingungen modifizieren), transferiert (mit anderen Parteien teilen) oder akzeptiert (einhergehende Chancen wahrnehmen).',
                'quote' => 'Risiken können vermieden werden, indem beispielsweise die Risikoursache ausgeschlossen wird, reduziert werden, indem die Rahmenbedingungen, die zur Risikoeinstufung beigetragen haben, modifiziert werden, transferiert werden, indem die Risiken mit anderen Parteien geteilt werden, akzeptiert werden, beispielsweise weil die mit dem Risiko einhergehenden Chancen wahrgenommen werden sollen.',
                'source' => 'BSI-Standard 200-3, Kapitel 6.1, S. 33',
                'answers' => [
                    ['text' => 'Vermeiden, reduzieren, transferieren und akzeptieren', 'is_correct' => true],
                    ['text' => 'Vermeiden, ignorieren, dokumentieren und eskalieren', 'is_correct' => false],
                    ['text' => 'Reduzieren, versichern, outsourcen und eliminieren', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Für welche Risikokategorien müssen laut BSI-Standard 200-3 die Risikobehandlungsoptionen einzeln geprüft werden?',
                'explanation' => 'Für jede Gefährdung mit Risikokategorie "mittel", "hoch" oder "sehr hoch" müssen die Risikobehandlungsoptionen Vermeidung, Reduktion, Transfer und Akzeptanz beantwortet werden. Geringe Risiken werden grundsätzlich akzeptiert.',
                'quote' => 'Für jede Gefährdung in der vervollständigten Gefährdungsübersicht mit Risikokategorie „mittel", „hoch" oder „sehr hoch" müssen folgende Fragen beantwortet werden: A: Risikovermeidung [...] B: Risikoreduktion [...] C: Risikotransfer [...] D: Risikoakzeptanz',
                'source' => 'BSI-Standard 200-3, Kapitel 6.1, S. 33',
                'answers' => [
                    ['text' => 'mittel, hoch und sehr hoch', 'is_correct' => true],
                    ['text' => 'Nur hoch und sehr hoch', 'is_correct' => false],
                    ['text' => 'Alle Risikokategorien einschließlich gering', 'is_correct' => false],
                    ['text' => 'Nur sehr hoch', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was versteht der BSI-Standard 200-3 unter "Risikovermeidung"?',
                'explanation' => 'Risikovermeidung fragt, ob es sinnvoll ist, das Risiko durch eine Umstrukturierung des Geschäftsprozesses oder des Informationsverbunds zu vermeiden. Gründe können sein: alle wirksamen Gegenmaßnahmen sind zu teuer, die Umstrukturierung bietet sich ohnehin an, oder alle Gegenmaßnahmen würden erhebliche Einschränkungen für den Komfort bedeuten.',
                'quote' => 'A: Risikovermeidung: Ist es sinnvoll, das Risiko durch eine Umstrukturierung des Geschäftsprozesses oder des Informationsverbunds zu vermeiden?',
                'source' => 'BSI-Standard 200-3, Kapitel 6.1, S. 33',
                'answers' => [
                    ['text' => 'Das Risiko durch Umstrukturierung des Geschäftsprozesses oder Informationsverbunds beseitigen', 'is_correct' => true],
                    ['text' => 'Das Risiko durch zusätzliche technische Sicherheitsmaßnahmen senken', 'is_correct' => false],
                    ['text' => 'Das Risiko an einen Versicherungsanbieter übertragen', 'is_correct' => false],
                    ['text' => 'Das Risiko bewusst akzeptieren und dokumentieren', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Wann kann laut BSI-Standard 200-3 ein höheres Risiko als "gering" akzeptiert werden?',
                'explanation' => 'Auch höhere Risiken können akzeptiert werden, wenn z.B. die Gefährdung nur unter speziellen Voraussetzungen eintreten kann, keine wirksamen Gegenmaßnahmen bekannt sind und die Gefährdung sich kaum vermeiden lässt, oder die Kosten für wirksame Gegenmaßnahmen den zu schützenden Wert überschreiten.',
                'quote' => 'In der Praxis ist dies aber nicht immer zweckmäßig sein, Gründe, auch höhere Risiken zu akzeptieren, können beispielsweise sein: Die entsprechende Gefährdung führt nur unter ganz speziellen Voraussetzungen zu einem Schaden. Gegen die entsprechende Gefährdung sind derzeit keine wirksamen Gegenmaßnahmen bekannt und sie lässt sich in der Praxis auch kaum vermeiden. Aufwand und Kosten für wirksame Gegenmaßnahmen überschreiten den zu schützenden Wert.',
                'source' => 'BSI-Standard 200-3, Kapitel 6.1, S. 34-35',
                'answers' => [
                    ['text' => 'Wenn die Gefährdung nur unter ganz speziellen Voraussetzungen zum Schaden führt', 'is_correct' => true],
                    ['text' => 'Wenn keine wirksamen Gegenmaßnahmen bekannt sind und die Gefährdung kaum vermeidbar ist', 'is_correct' => true],
                    ['text' => 'Wenn Aufwand und Kosten für Gegenmaßnahmen den zu schützenden Wert überschreiten', 'is_correct' => true],
                    ['text' => 'Wenn die IT-Abteilung das Risiko für unbedeutend hält', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Wer muss dem Restrisiko nach der Risikobehandlung laut BSI-Standard 200-3 zustimmen?',
                'explanation' => 'Das Restrisiko muss der Leitungsebene zur Zustimmung vorgelegt werden (Risikoakzeptanz). Die Institution muss nachvollziehbar dokumentieren, dass sie sich des Restrisikos bewusst ist.',
                'quote' => 'Das Restrisiko muss anschließend der Leitungsebene zur Zustimmung vorgelegt werden (Risikoakzeptanz). Damit wird nachvollziehbar dokumentiert, dass die Institution sich des Restrisikos bewusst ist.',
                'source' => 'BSI-Standard 200-3, Kapitel 6.1, S. 34',
                'answers' => [
                    ['text' => 'Die Leitungsebene', 'is_correct' => true],
                    ['text' => 'Der Informationssicherheitsbeauftragte allein', 'is_correct' => false],
                    ['text' => 'Die IT-Abteilung', 'is_correct' => false],
                    ['text' => 'Ein externer Auditor', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was ist beim Risikotransfer laut BSI-Standard 200-3 einer der wichtigsten Aspekte?',
                'explanation' => 'Beim Risikotransfer ist die sachgerechte Vertragsgestaltung einer der wichtigsten Aspekte. Besonders bei Outsourcing-Vorhaben sollte auf juristischen Sachverstand zurückgegriffen werden.',
                'quote' => 'Beim Risikotransfer ist die sachgerechte Vertragsgestaltung einer der wichtigsten Aspekte. Besonders bei Outsourcing-Vorhaben sollte hierzu auf juristischen Sachverstand zurückgegriffen werden.',
                'source' => 'BSI-Standard 200-3, Kapitel 6.1, S. 34',
                'answers' => [
                    ['text' => 'Die sachgerechte Vertragsgestaltung', 'is_correct' => true],
                    ['text' => 'Die Auswahl des günstigsten Anbieters', 'is_correct' => false],
                    ['text' => 'Die vollständige Übertragung der Verantwortung an Dritte', 'is_correct' => false],
                    ['text' => 'Die Einhaltung einer maximalen Vertragslaufzeit von einem Jahr', 'is_correct' => false],
                ],
            ],
            // === Kapitel 6.2: Risiken unter Beobachtung ===
            [
                'text' => 'Was bedeutet es, ein Risiko "unter Beobachtung" zu stellen?',
                'explanation' => 'Bei der Risikoanalyse können Gefährdungen identifiziert werden, aus denen Risiken resultieren, die aktuell akzeptabel sind, aber voraussichtlich in Zukunft steigen werden. Es ist sinnvoll, bereits im Vorfeld ergänzende Sicherheitsmaßnahmen zu erarbeiten und vorzubereiten, die in Betrieb genommen werden können, sobald die Risiken inakzeptabel werden.',
                'quote' => 'Bei der Risikoanalyse können unter Umständen Gefährdungen identifiziert werden, aus denen Risiken resultieren, die zwar derzeit akzeptabel sind, in Zukunft jedoch voraussichtlich steigen werden. [...] Es ist sinnvoll und üblich, bereits im Vorfeld ergänzende Sicherheitsmaßnahmen zu erarbeiten und vorzubereiten, die in Betrieb genommen werden können, sobald die Risiken inakzeptabel werden.',
                'source' => 'BSI-Standard 200-3, Kapitel 6.2, S. 35',
                'answers' => [
                    ['text' => 'Aktuell akzeptable Risiken, die voraussichtlich steigen werden, werden dokumentiert und Maßnahmen werden vorsorglich vorbereitet', 'is_correct' => true],
                    ['text' => 'Das Risiko wird dauerhaft ignoriert und nicht weiter betrachtet', 'is_correct' => false],
                    ['text' => 'Es wird sofort eine Sicherheitsmaßnahme implementiert', 'is_correct' => false],
                    ['text' => 'Das Risiko wird automatisch an eine Versicherung übertragen', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussage zu Risiken unter Beobachtung ist NICHT korrekt?',
                'explanation' => 'Generell sollten alle Risiken beobachtet werden, nicht nur solche, die voraussichtlich steigen werden. Für benutzerdefinierte Bausteine müssen die Gefährdungen in regelmäßigen Zeitabständen überprüft und neu bewertet werden.',
                'quote' => 'Generell sollten jedoch alle Risiken beobachtet werden, also nicht nur solche, die in Zukunft voraussichtlich steigen. [...] Für benutzerdefinierte Bausteine müssen die Gefährdungen in regelmäßigen Zeitabständen überprüft und neu bewertet werden.',
                'source' => 'BSI-Standard 200-3, Kapitel 6.2, S. 35',
                'answers' => [
                    ['text' => 'Nur Risiken mit der Kategorie "sehr hoch" müssen beobachtet werden', 'is_correct' => true],
                    ['text' => 'Alle Risiken sollten beobachtet werden, nicht nur die voraussichtlich steigenden', 'is_correct' => false],
                    ['text' => 'Benutzerdefinierte Bausteine erfordern regelmäßige Neubewertung der Gefährdungen', 'is_correct' => false],
                ],
            ],
            // === Kapitel 7: Konsolidierung des Sicherheitskonzepts ===
            [
                'text' => 'Welche Prüfkriterien sind bei der Konsolidierung des Sicherheitskonzepts zu beachten?',
                'explanation' => 'Bei der Konsolidierung müssen die Sicherheitsmaßnahmen für jedes Zielobjekt anhand folgender Kriterien geprüft werden: Eignung zur Abwehr der Gefährdungen, Zusammenwirken der Maßnahmen, Benutzerfreundlichkeit und Angemessenheit/Qualitätssicherung.',
                'quote' => 'Eignung der Sicherheitsmaßnahmen zur Abwehr der Gefährdungen [...] Zusammenwirken der Sicherheitsmaßnahmen [...] Benutzerfreundlichkeit der Sicherheitsmaßnahmen [...] Angemessenheit/Qualitätssicherung der Sicherheitsmaßnahmen',
                'source' => 'BSI-Standard 200-3, Kapitel 7, S. 39',
                'answers' => [
                    ['text' => 'Eignung der Maßnahmen zur Abwehr der Gefährdungen', 'is_correct' => true],
                    ['text' => 'Zusammenwirken der Sicherheitsmaßnahmen', 'is_correct' => true],
                    ['text' => 'Benutzerfreundlichkeit der Maßnahmen', 'is_correct' => true],
                    ['text' => 'Angemessenheit und Qualitätssicherung', 'is_correct' => true],
                    ['text' => 'Minimierung der Gesamtkosten um jeden Preis', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Wie sollte laut BSI-Standard 200-3 mit ungeeigneten oder widersprüchlichen Sicherheitsmaßnahmen bei der Konsolidierung umgegangen werden?',
                'explanation' => 'Ungeeignete Maßnahmen sollten verworfen und durch wirksame ersetzt werden. Widersprüche oder Inkonsistenzen bei Sicherheitsmaßnahmen sollten aufgelöst und durch einheitliche, aufeinander abgestimmte Mechanismen ersetzt werden. Maßnahmen, die von Betroffenen nicht akzeptiert werden, sind wirkungslos.',
                'quote' => 'Ungeeignete Sicherheitsmaßnahmen sollten verworfen und nach eingehender Analyse durch wirksame Maßnahmen ersetzt werden. Widersprüche oder Inkonsistenzen bei den Sicherheitsmaßnahmen sollten aufgelöst und durch einheitliche und aufeinander abgestimmte Mechanismen ersetzt werden. Sicherheitsmaßnahmen, die von den Betroffenen nicht akzeptiert werden, sind wirkungslos.',
                'source' => 'BSI-Standard 200-3, Kapitel 7, S. 39',
                'answers' => [
                    ['text' => 'Ungeeignete Maßnahmen verwerfen und durch wirksame ersetzen', 'is_correct' => true],
                    ['text' => 'Widersprüche auflösen und durch einheitliche Mechanismen ersetzen', 'is_correct' => true],
                    ['text' => 'Von Betroffenen nicht akzeptierte Maßnahmen als wirkungslos betrachten', 'is_correct' => true],
                    ['text' => 'Alle bestehenden Maßnahmen beibehalten, auch wenn sie sich widersprechen', 'is_correct' => false],
                ],
            ],
            // === Kapitel 8: Rückführung in den Sicherheitsprozess ===
            [
                'text' => 'Welche Arbeitsschritte dient das konsolidierte Sicherheitskonzept nach der Risikoanalyse als Basis?',
                'explanation' => 'Das ergänzte Sicherheitskonzept dient als Basis für: IT-Grundschutz-Check (Prüfung neuer/geänderter Anforderungen), Umsetzung der Sicherheitskonzeption, Überprüfung des IS-Prozesses in allen Ebenen, Informationsfluss im IS-Prozess und ISO 27001-Zertifizierung auf Basis von IT-Grundschutz.',
                'quote' => 'IT-Grundschutz-Check [...] Umsetzung der Sicherheitskonzeption [...] Überprüfung des Informationssicherheitsprozesses in allen Ebenen [...] Informationsfluss im Informationssicherheitsprozess [...] ISO 27001-Zertifizierung auf der Basis von IT-Grundschutz',
                'source' => 'BSI-Standard 200-3, Kapitel 8, S. 41',
                'answers' => [
                    ['text' => 'IT-Grundschutz-Check und Umsetzung der Sicherheitskonzeption', 'is_correct' => true],
                    ['text' => 'Überprüfung des IS-Prozesses in allen Ebenen', 'is_correct' => true],
                    ['text' => 'ISO 27001-Zertifizierung auf der Basis von IT-Grundschutz', 'is_correct' => true],
                    ['text' => 'Automatische Abschaltung aller unsicheren IT-Systeme', 'is_correct' => false],
                ],
            ],
            // === Kapitel 9: Anhang - Risikoappetit ===
            [
                'text' => 'Was bezeichnet der Begriff "Risikoappetit" (Risikobereitschaft) im Kontext des BSI-Standard 200-3?',
                'explanation' => 'Risikoappetit bezeichnet die durch kulturelle, interne, externe oder wirtschaftliche Einflüsse entstandene Neigung einer Institution, wie sie Risiken einschätzt, bewertet und mit ihnen umgeht.',
                'quote' => 'Risikoappetit bezeichnet die durch kulturelle, interne, externe oder wirtschaftliche Einflüsse entstandene Neigung einer Institution, wie sie Risiken einschätzt, bewertet und mit ihnen umgeht.',
                'source' => 'BSI-Standard 200-3, Kapitel 9.1, S. 42',
                'answers' => [
                    ['text' => 'Die durch kulturelle, interne, externe oder wirtschaftliche Einflüsse entstandene Neigung einer Institution im Umgang mit Risiken', 'is_correct' => true],
                    ['text' => 'Die maximale Anzahl an Risiken, die eine Institution gleichzeitig behandeln kann', 'is_correct' => false],
                    ['text' => 'Ein quantitativer Schwellenwert in Euro, ab dem Risiken behandelt werden müssen', 'is_correct' => false],
                    ['text' => 'Die persönliche Risikobereitschaft des IT-Administrators', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche äußeren Einflussfaktoren beeinflussen die Risikoneigung einer Institution?',
                'explanation' => 'Zu den äußeren Bedingungen gehören kulturelle Einflüsse (je nach Land unterschiedliche Risikobereitschaft) und interne Faktoren wie Organisationskultur und Einstellung des Managements (Risiken als Problem oder Chance).',
                'quote' => 'Zu den äußeren Bedingungen, die die Risikoneigung einer Institution beeinflussen, gehören: Kulturelle Einflüsse (Je nach Land und Mentalität gibt es unterschiedliche Bereitschaften, Risiken einzugehen.) Interne Faktoren (Organisationskultur, Einstellung des Managements, Risiken als Problem oder Chance sehen)',
                'source' => 'BSI-Standard 200-3, Kapitel 9.1.1, S. 42',
                'answers' => [
                    ['text' => 'Kulturelle Einflüsse je nach Land und Mentalität', 'is_correct' => true],
                    ['text' => 'Interne Faktoren wie Organisationskultur und Einstellung des Managements', 'is_correct' => true],
                    ['text' => 'Die Anzahl der IT-Mitarbeiter in der Institution', 'is_correct' => false],
                    ['text' => 'Die verwendete Programmiersprache der IT-Systeme', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Risikotypen werden im BSI-Standard 200-3 für Institutionen beschrieben?',
                'explanation' => 'Der Standard beschreibt verschiedene Risikotypen: "Cowboy" (nimmt Risiken prinzipiell bereitwillig in Kauf), "Risk-Eater" (nimmt hohe Risiken in Kauf bei hohen Chancen), "Konservativer" (versucht alle Risiken durch Maßnahmen zu minimieren), "Risikoaffiner" (sieht stets die Chancen hinter Risiken) und "Unsicherheitsvermeider" (versucht verlässliche Daten zu sammeln).',
                'quote' => '„Cowboy" – entspricht einer Institution, die Risiken prinzipiell bereitwillig in Kauf nimmt „Risk-Eater" – nimmt hohe Risiken in Kauf, wenn diesen auch hohe Chancen gegenüberstehen „Konservativer" – versucht, alle Risiken durch Maßnahmen so weit wie möglich zu minimieren',
                'source' => 'BSI-Standard 200-3, Kapitel 9.1.2, S. 47',
                'answers' => [
                    ['text' => 'Cowboy: nimmt Risiken prinzipiell bereitwillig in Kauf', 'is_correct' => true],
                    ['text' => 'Konservativer: versucht alle Risiken durch Maßnahmen zu minimieren', 'is_correct' => true],
                    ['text' => 'Risk-Eater: nimmt hohe Risiken bei hohen Chancen in Kauf', 'is_correct' => true],
                    ['text' => 'Optimist: geht davon aus, dass keine Risiken eintreten werden', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was empfiehlt der BSI-Standard 200-3 für Institutionen mit hoher Risikoneigung (Typus "Risikoaffin")?',
                'explanation' => 'Wer sich zu einer hohen Risikoneigung bekennt, sollte dem in seinem Risikomanagementprozess und seiner Organisationsstruktur Rechnung tragen. Hohe Risiken verlangen eine aufmerksame Verfolgung und Kontrolle.',
                'quote' => 'Generell lautet die Empfehlung: Wer sich zu einer hohen Risikoneigung bekannt hat (Typus „Risikoaffiner"), sollte dem in seinem Risikomanagementprozess und seiner Organisationsstruktur Rechnung tragen. Hohe Risiken verlangen eine aufmerksame Verfolgung und Kontrolle.',
                'source' => 'BSI-Standard 200-3, Kapitel 9.1.3, S. 48',
                'answers' => [
                    ['text' => 'Den Risikomanagementprozess und die Organisationsstruktur an die hohe Risikoneigung anpassen', 'is_correct' => true],
                    ['text' => 'Hohe Risiken aufmerksam verfolgen und kontrollieren', 'is_correct' => true],
                    ['text' => 'Auf jegliches Risikomanagement verzichten, da die Institution risikoaffin ist', 'is_correct' => false],
                    ['text' => 'Alle Risiken pauschal auf Versicherungen übertragen', 'is_correct' => false],
                ],
            ],
            // === Kapitel 9.1.3: Risikoneigung als Eingangsgröße im ISMS ===
            [
                'text' => 'Wer gibt laut BSI-Standard 200-3 die Risikoneigung vor?',
                'explanation' => 'Die Leitungsebene gibt die Risikoneigung vor. Das Sicherheitsmanagement muss die Risikoneigung kennen und sie entsprechend umsetzen. Falls die Institution sich ihrer Risikoneigung nicht bewusst ist, sollte das Management eine Klärung herbeiführen.',
                'quote' => 'Die Leitungsebene gibt die Risikoneigung vor. Das Sicherheitsmanagement muss die Risikoneigung kennen und sie entsprechend umsetzen.',
                'source' => 'BSI-Standard 200-3, Kapitel 9.1.3, S. 48',
                'answers' => [
                    ['text' => 'Die Leitungsebene', 'is_correct' => true],
                    ['text' => 'Der IT-Administrator', 'is_correct' => false],
                    ['text' => 'Der externe Auditor', 'is_correct' => false],
                    ['text' => 'Die Personalabteilung', 'is_correct' => false],
                ],
            ],
            // === Kapitel 9.2: Moderation der Risikoanalyse ===
            [
                'text' => 'Welche Empfehlungen gibt der BSI-Standard 200-3 zur Moderation der Risikoanalyse?',
                'explanation' => 'Der Standard empfiehlt: Fachverantwortliche bzw. Experten für die betrachteten Zielobjekte einbeziehen, mehrere kurze Sitzungen statt einer langen, ein Moderator sollte benannt werden, ein Vertreter der Leitungsebene sollte zumindest gegen Ende anwesend sein, und das Team sollte vier bis acht Personen umfassen.',
                'quote' => 'In der Praxis hat es sich bewährt, hierzu besser mehrere kurze als eine lange Sitzung mit allen beteiligten Mitarbeitern durchzuführen. [...] Es sollte ein Moderator benannt werden. [...] Das Team sollte nicht zu groß sein (bewährt haben sich vier bis acht Personen).',
                'source' => 'BSI-Standard 200-3, Kapitel 9.2, S. 49',
                'answers' => [
                    ['text' => 'Mehrere kurze Sitzungen statt einer langen Sitzung', 'is_correct' => true],
                    ['text' => 'Ein Moderator sollte benannt werden', 'is_correct' => true],
                    ['text' => 'Die ideale Teamgröße beträgt vier bis acht Personen', 'is_correct' => true],
                    ['text' => 'Möglichst viele Teilnehmer (mindestens 20) einbeziehen', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Empfehlung gibt der BSI-Standard 200-3 zur zeitlichen Durchführung einer Risikoanalyse?',
                'explanation' => 'Auch eine Risikoanalyse für komplexe Sachverhalte ist normalerweise an einem Tag zu schaffen. Wenn der Bereich zu umfangreich ist, sollte er in Teilbereiche aufgeteilt werden. Außerdem sollte die 80:20-Regel beachtet werden.',
                'quote' => 'Auch eine Risikoanalyse für komplexe Sachverhalte ist normalerweise an einem Tag zu schaffen. Wenn der betrachtete Bereich zu umfangreich ist, sollte er in Teilbereiche aufgeteilt werden. Auch bei einer Risikoanalyse sollte die 80:20-Regel beachtet werden.',
                'source' => 'BSI-Standard 200-3, Kapitel 9.2, S. 50',
                'answers' => [
                    ['text' => 'Auch komplexe Risikoanalysen sind normalerweise an einem Tag zu schaffen', 'is_correct' => true],
                    ['text' => 'Bei zu umfangreichen Bereichen sollte in Teilbereiche aufgeteilt werden', 'is_correct' => true],
                    ['text' => 'Eine Risikoanalyse erfordert mindestens eine Woche intensive Arbeit', 'is_correct' => false],
                    ['text' => 'Die Analyse darf nur am Wochenende durchgeführt werden', 'is_correct' => false],
                ],
            ],
            // === Kapitel 9.4: Zusammenspiel mit ISO/IEC 31000 ===
            [
                'text' => 'Welchem ISO/IEC 31000-Schritt entspricht die Erstellung einer Gefährdungsübersicht (Kapitel 4) im BSI-Standard 200-3?',
                'explanation' => 'Die Erstellung einer Gefährdungsübersicht aus Kapitel 4 des BSI-Standard 200-3 entspricht dem Schritt "Risk Identification" (Kapitel 5.4.2) aus der ISO/IEC 31000.',
                'quote' => 'Risk Identification, Kapitel 5.4.2: BSI-Standard 200-3 Erstellung einer Gefährdungsübersicht, Kapitel 4',
                'source' => 'BSI-Standard 200-3, Kapitel 9.4, S. 51',
                'answers' => [
                    ['text' => 'Risk Identification (Kapitel 5.4.2)', 'is_correct' => true],
                    ['text' => 'Risk Analysis (Kapitel 5.4.3)', 'is_correct' => false],
                    ['text' => 'Risk Evaluation (Kapitel 5.4.4)', 'is_correct' => false],
                    ['text' => 'Establishing the Context (Kapitel 5.3)', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Fragestellungen sollten bei der Ermittlung zusätzlicher Gefährdungen laut Anhang berücksichtigt werden?',
                'explanation' => 'Bei der Ermittlung zusätzlicher Gefährdungen sollten Fragen zu höherer Gewalt, organisatorischen Mängeln, menschlichen Fehlhandlungen, technischem Versagen, vorsätzlichen Angriffen von Außentätern und Innentätern sowie externen Objekten berücksichtigt werden.',
                'quote' => 'Von welchen Ereignissen aus dem Bereich höhere Gewalt droht besondere Gefahr für den Informationsverbund? Welche organisatorischen Mängel müssen vermieden werden, um die Informationssicherheit zu gewährleisten? Welche menschlichen Fehlhandlungen können die Sicherheit der Informationen besonders beeinträchtigen? [...] Welche besondere Gefahr droht durch vorsätzliche Angriffe von Außentätern? [...] Auf welche Weise können Innentäter durch vorsätzliche Handlungen den ordnungsgemäßen und sicheren Betrieb des jeweiligen Zielobjekts beeinträchtigen?',
                'source' => 'BSI-Standard 200-3, Kapitel 9.3, S. 50',
                'answers' => [
                    ['text' => 'Höhere Gewalt und organisatorische Mängel', 'is_correct' => true],
                    ['text' => 'Menschliche Fehlhandlungen und technisches Versagen', 'is_correct' => true],
                    ['text' => 'Vorsätzliche Angriffe von Außen- und Innentätern', 'is_correct' => true],
                    ['text' => 'Die Verfügbarkeit von Parkplätzen für Mitarbeiter', 'is_correct' => false],
                ],
            ],
            // === Kapitel 9.1.4: Auswirkung von Gesetzen und Regularien ===
            [
                'text' => 'Wie beeinflussen Gesetze und Normen laut BSI-Standard 200-3 den Umgang mit Risiken?',
                'explanation' => 'Gesetze und Normen beeinflussen nicht die Risikoneigung an sich, aber den Umgang mit Risiken. Die Risiken nehmen durch regulatorischen Druck zu, da sich das Verhältnis von Risikoappetit zu den ursprünglichen Risiken verschieben kann. Jede Institution muss Sanktionen aufgrund von Verstößen in ihr Risikokalkül aufnehmen.',
                'quote' => 'Gesetze und Normen beeinflussen nicht die Risikoneigung einer Institution an sich, aber den Umgang mit Risiken. Die Risiken nehmen durch regulatorischen Druck zu, sodass sich das Verhältnis von Risikoappetit zu den ursprünglichen Risiken verschieben kann. Jede Institution muss Sanktionen aufgrund von rechtlichen oder vertraglichen Verstößen in ihr Risikokalkül mit aufnehmen.',
                'source' => 'BSI-Standard 200-3, Kapitel 9.1.4, S. 49',
                'answers' => [
                    ['text' => 'Sie beeinflussen nicht die Risikoneigung selbst, aber den Umgang mit Risiken', 'is_correct' => true],
                    ['text' => 'Regulatorischer Druck kann das Verhältnis von Risikoappetit zu Risiken verschieben', 'is_correct' => true],
                    ['text' => 'Sanktionen aufgrund von Verstößen müssen ins Risikokalkül aufgenommen werden', 'is_correct' => true],
                    ['text' => 'Gesetze eliminieren alle Risiken automatisch', 'is_correct' => false],
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
