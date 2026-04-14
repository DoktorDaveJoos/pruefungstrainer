<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Database\Seeder;

class BsiStandard2002QuestionsSeeder extends Seeder
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
                'text' => 'Welchen BSI-Standard löst der BSI-Standard 200-2 ab?',
                'explanation' => 'Der BSI-Standard 200-2 löst den BSI-Standard 100-2 ab. Dies geht aus der Versionshistorie hervor. Der BSI-Standard 200-1 behandelt ISMS-Anforderungen, 200-3 die Risikoanalyse und 200-4 das Notfallmanagement.',
                'quote' => 'Der BSI-Standard 200-2 löst den BSI-Standard 100-2 ab.',
                'source' => 'BSI-Standard 200-2, Kapitel 1.1, S. 7',
                'answers' => [
                    ['text' => 'BSI-Standard 100-2', 'is_correct' => true],
                    ['text' => 'BSI-Standard 100-1', 'is_correct' => false],
                    ['text' => 'BSI-Standard 200-1', 'is_correct' => false],
                    ['text' => 'BSI-Standard 100-4', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Vorgehensweisen zur Absicherung werden im BSI-Standard 200-2 beschrieben?',
                'explanation' => 'Der BSI-Standard 200-2 beschreibt drei Vorgehensweisen: Standard-Absicherung, Basis-Absicherung und Kern-Absicherung. Die Erweiterte Absicherung ist keine im Standard definierte Vorgehensweise.',
                'quote' => 'Im BSI-Standard 200-2 wird dies über die drei Vorgehensweisen „Standard-Absicherung", „Basis-Absicherung" und „Kern-Absicherung" realisiert.',
                'source' => 'BSI-Standard 200-2, Kapitel 1.2, S. 7',
                'answers' => [
                    ['text' => 'Standard-Absicherung', 'is_correct' => true],
                    ['text' => 'Basis-Absicherung', 'is_correct' => true],
                    ['text' => 'Kern-Absicherung', 'is_correct' => true],
                    ['text' => 'Erweiterte Absicherung', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'An welche Zielgruppe richtet sich der BSI-Standard 200-2 primär?',
                'explanation' => 'Der BSI-Standard 200-2 richtet sich primär an Sicherheitsverantwortliche, -beauftragte, -experten und -berater sowie alle, die mit dem Management von Informationssicherheit betraut sind. Er ist zugleich eine Grundlage für IT- und ICS-Verantwortliche, Führungskräfte und Projektmanager.',
                'quote' => 'Der BSI-Standard 200-2 richtet sich primär an Sicherheitsverantwortliche, -beauftragte, -experten, -berater und alle Interessierten, die mit dem Management von Informationssicherheit betraut sind. Er ist zugleich eine sinnvolle Grundlage für IT- und ICS-Verantwortliche, Führungskräfte und Projektmanager.',
                'source' => 'BSI-Standard 200-2, Kapitel 1.3, S. 8',
                'answers' => [
                    ['text' => 'Sicherheitsverantwortliche und -beauftragte', 'is_correct' => true],
                    ['text' => 'Führungskräfte und Projektmanager', 'is_correct' => true],
                    ['text' => 'Ausschließlich IT-Administratoren', 'is_correct' => false],
                    ['text' => 'Endanwender ohne Sicherheitsverantwortung', 'is_correct' => false],
                ],
            ],

            // === Kapitel 2: Informationssicherheitsmanagement mit IT-Grundschutz ===
            [
                'text' => 'Welche Grundwerte der Informationssicherheit werden im IT-Grundschutz betrachtet?',
                'explanation' => 'Die drei Grundwerte der Informationssicherheit sind Vertraulichkeit, Integrität und Verfügbarkeit. Zusätzlich können Authentizität und Nicht-Abstreitbarkeit als Spezialfälle der Integrität betrachtet werden. Verbindlichkeit ist kein eigenständiger Grundwert im IT-Grundschutz.',
                'quote' => 'Aufgabe der Informationssicherheit ist der angemessene Schutz der Grundwerte Vertraulichkeit, Integrität (Unverfälschtheit) und Verfügbarkeit von Informationen.',
                'source' => 'BSI-Standard 200-2, Kapitel 2.5, S. 14',
                'answers' => [
                    ['text' => 'Vertraulichkeit', 'is_correct' => true],
                    ['text' => 'Integrität', 'is_correct' => true],
                    ['text' => 'Verfügbarkeit', 'is_correct' => true],
                    ['text' => 'Verbindlichkeit', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'In welche Schichten sind die prozessorientierten Bausteine des IT-Grundschutz-Kompendiums gruppiert?',
                'explanation' => 'Die prozessorientierten Bausteine sind in die Schichten ISMS, ORP, CON, OPS und DER gruppiert. INF, NET, SYS, APP und IND sind systemorientierte Bausteine.',
                'quote' => 'Die prozessorientierten Bausteine finden sich in den folgenden Schichten: ISMS (Managementsysteme für Informationssicherheit), ORP (Organisation und Personal), CON (Konzepte und Vorgehensweisen), OPS (Betrieb), DER (Detektion und Reaktion)',
                'source' => 'BSI-Standard 200-2, Kapitel 2.4, S. 13',
                'answers' => [
                    ['text' => 'ISMS (Managementsysteme für Informationssicherheit)', 'is_correct' => true],
                    ['text' => 'ORP (Organisation und Personal)', 'is_correct' => true],
                    ['text' => 'CON (Konzepte und Vorgehensweisen)', 'is_correct' => true],
                    ['text' => 'OPS (Betrieb)', 'is_correct' => true],
                    ['text' => 'DER (Detektion und Reaktion)', 'is_correct' => true],
                    ['text' => 'NET (Netze und Kommunikation)', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche der folgenden Schichten gehören NICHT zu den prozessorientierten Bausteinen?',
                'explanation' => 'INF (Infrastruktur) und SYS (IT-Systeme) sind systemorientierte Bausteine. ORP (Organisation und Personal) und CON (Konzepte und Vorgehensweisen) sind prozessorientierte Bausteine.',
                'quote' => 'Die systemorientierten Bausteine sind in die folgenden Schichten gruppiert: INF (Infrastruktur), NET (Netze und Kommunikation), SYS (IT-Systeme), APP (Anwendungen), IND (Industrielle IT)',
                'source' => 'BSI-Standard 200-2, Kapitel 2.4, S. 13',
                'answers' => [
                    ['text' => 'INF (Infrastruktur)', 'is_correct' => true],
                    ['text' => 'SYS (IT-Systeme)', 'is_correct' => true],
                    ['text' => 'ORP (Organisation und Personal)', 'is_correct' => false],
                    ['text' => 'CON (Konzepte und Vorgehensweisen)', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Bedeutung haben die Modalverben "MUSS" und "SOLLTE" in den Anforderungen des IT-Grundschutz-Kompendiums?',
                'explanation' => '"MUSS/DARF NUR" bedeutet eine uneingeschränkte Anforderung, die unbedingt erfüllt werden muss. "SOLLTE" bedeutet, dass die Anforderung normalerweise erfüllt werden muss, es aber Gründe geben kann, dies nicht zu tun — diese müssen sorgfältig abgewogen und stichhaltig begründet werden.',
                'quote' => 'MUSS/DARF NUR: Dieser Ausdruck bedeutet, dass es sich um eine Anforderung handelt, die unbedingt erfüllt werden muss (uneingeschränkte Anforderung). [...] SOLLTE: Dieser Ausdruck bedeutet, dass eine Anforderung normalerweise erfüllt werden muss, es Gründe geben kann, dies doch nicht zu tun. Dies muss aber sorgfältig abgewogen und stichhaltig begründet werden.',
                'source' => 'BSI-Standard 200-2, Kapitel 2.7, S. 18',
                'answers' => [
                    ['text' => '"MUSS" ist eine uneingeschränkte Anforderung', 'is_correct' => true],
                    ['text' => '"SOLLTE" erlaubt begründete Abweichungen', 'is_correct' => true],
                    ['text' => '"MUSS" und "SOLLTE" sind gleichbedeutend', 'is_correct' => false],
                    ['text' => '"SOLLTE" ist lediglich eine unverbindliche Empfehlung', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Aus welchen Hauptphasen besteht der Sicherheitsprozess nach IT-Grundschutz?',
                'explanation' => 'Der Sicherheitsprozess besteht aus: Initiierung des Sicherheitsprozesses, Erstellung der Leitlinie zur Informationssicherheit, Organisation des Sicherheitsprozesses, Erstellung einer Sicherheitskonzeption, Umsetzung der Sicherheitskonzeption und Aufrechterhaltung und Verbesserung. Eine separate "Risikobewertungsphase" ist kein eigenständiger Hauptschritt, sondern Teil der Sicherheitskonzeption.',
                'quote' => 'Im Rahmen des IT-Grundschutzes besteht der Sicherheitsprozess aus den folgenden Phasen: Initiierung des Sicherheitsprozesses, Erstellung der Leitlinie zur Informationssicherheit, Aufbau einer geeigneten Organisationsstruktur für das Informationssicherheitsmanagement, Erstellung einer Sicherheitskonzeption, Umsetzung der Sicherheitskonzeption, Aufrechterhaltung und kontinuierliche Verbesserung der Informationssicherheit',
                'source' => 'BSI-Standard 200-2, Kapitel 2.6, S. 14-15',
                'answers' => [
                    ['text' => 'Initiierung des Sicherheitsprozesses', 'is_correct' => true],
                    ['text' => 'Erstellung der Leitlinie zur Informationssicherheit', 'is_correct' => true],
                    ['text' => 'Erstellung einer Sicherheitskonzeption', 'is_correct' => true],
                    ['text' => 'Aufrechterhaltung und Verbesserung', 'is_correct' => true],
                    ['text' => 'Risikobewertungsphase', 'is_correct' => false],
                ],
            ],

            // === Kapitel 3: Initiierung des Sicherheitsprozesses ===
            [
                'text' => 'Wer muss laut BSI-Standard 200-2 den Sicherheitsprozess initiieren?',
                'explanation' => 'Die oberste Leitungsebene muss den Sicherheitsprozess initiieren, steuern und kontrollieren. Die Verantwortung für Informationssicherheit verbleibt dort. Die operative Aufgabe wird allerdings typischerweise an einen Informationssicherheitsbeauftragten (ISB) delegiert.',
                'quote' => 'Die oberste Leitungsebene muss den Sicherheitsprozess initiieren, steuern und kontrollieren. Die Verantwortung für Informationssicherheit verbleibt dort. Die operative Aufgabe „Informationssicherheit" wird allerdings typischerweise an einen Informationssicherheitsbeauftragten (ISB) delegiert.',
                'source' => 'BSI-Standard 200-2, Kapitel 3.1, S. 20',
                'answers' => [
                    ['text' => 'Die oberste Leitungsebene', 'is_correct' => true],
                    ['text' => 'Der Informationssicherheitsbeauftragte', 'is_correct' => false],
                    ['text' => 'Der IT-Leiter', 'is_correct' => false],
                    ['text' => 'Der Datenschutzbeauftragte', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussagen treffen auf die Basis-Absicherung zu?',
                'explanation' => 'Die Basis-Absicherung verfolgt das Ziel einer breiten, grundlegenden Erst-Absicherung. Sie ist empfehlenswert bei niedrigem Reifegrad, normalem Schutzbedarf und wenn keine existenzbedrohenden Assets vorhanden sind. Eine Zertifizierung nach ISO 27001 ist auf dieser Basis NICHT möglich — dafür ist mindestens die Standard-Absicherung erforderlich.',
                'quote' => 'Die Basis-Absicherung verfolgt das Ziel, als Einstieg in den IT-Grundschutz zunächst eine breite, grundlegende Erst-Absicherung über alle relevanten Geschäftsprozesse bzw. Fachverfahren einer Institution hinweg zu erlangen.',
                'source' => 'BSI-Standard 200-2, Kapitel 3.3.1, S. 29',
                'answers' => [
                    ['text' => 'Sie dient als Einstieg in den IT-Grundschutz', 'is_correct' => true],
                    ['text' => 'Sie bietet eine breite, grundlegende Erst-Absicherung', 'is_correct' => true],
                    ['text' => 'Sie ermöglicht eine Zertifizierung nach ISO 27001', 'is_correct' => false],
                    ['text' => 'Sie setzt einen hohen Reifegrad der Informationssicherheit voraus', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Wann ist die Kern-Absicherung als Vorgehensweise besonders empfehlenswert?',
                'explanation' => 'Die Kern-Absicherung ist empfehlenswert, wenn die Menge der Geschäftsprozesse mit deutlich erhöhtem Schutzbedarf überschaubar ist, die Institution eindeutig benennbare Kronjuwelen besitzt (deren Diebstahl, Zerstörung oder Kompromittierung existenzbedrohend wäre) und kleinere Sicherheitsvorfälle akzeptabel sind. Sie ist NICHT für Institutionen gedacht, die bereits ein hohes Sicherheitsniveau erreicht haben.',
                'quote' => 'Diese Vorgehensweise ist empfehlenswert, wenn für eine Institution folgende Aspekte überwiegend zutreffen: Die Menge der Geschäftsprozesse mit deutlich erhöhtem Schutzbedarf ist überschaubar [...] Die Institution besitzt eindeutig benennbare Assets, deren Diebstahl, Zerstörung oder Kompromittierung einen existenzbedrohenden Schaden für die Institution bedeuten würde (sogenannte Kronjuwelen).',
                'source' => 'BSI-Standard 200-2, Kapitel 3.3.2, S. 29',
                'answers' => [
                    ['text' => 'Wenn die Institution eindeutig benennbare Kronjuwelen besitzt', 'is_correct' => true],
                    ['text' => 'Wenn nur wenige Geschäftsprozesse mit erhöhtem Schutzbedarf existieren', 'is_correct' => true],
                    ['text' => 'Wenn die Institution bereits ein hohes Sicherheitsniveau erreicht hat', 'is_correct' => false],
                    ['text' => 'Wenn alle Geschäftsprozesse gleich schutzbedürftig sind', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussagen treffen auf die Standard-Absicherung zu?',
                'explanation' => 'Die Standard-Absicherung entspricht im Wesentlichen der klassischen IT-Grundschutz-Vorgehensweise. Sie ist die vom BSI präferierte Vorgehensweise und bietet ein gleichmäßiges Sicherheitsniveau über die gesamte Institution. Eine Zertifizierung nach ISO 27001 ist möglich. Ein Vorteil ist die Messbarkeit des ISMS.',
                'quote' => 'Die dritte und vom BSI präferierte Vorgehensweise ist die Standard-Absicherung. [...] Die Standard-Absicherung entspricht im Wesentlichen der klassischen IT-Grundschutz-Vorgehensweise nach BSI-Standard 100-2. [...] Eine Zertifizierung nach ISO 27001 und eine Messbarkeit des ISMS sind möglich.',
                'source' => 'BSI-Standard 200-2, Kapitel 3.3.3, S. 30',
                'answers' => [
                    ['text' => 'Sie ist die vom BSI präferierte Vorgehensweise', 'is_correct' => true],
                    ['text' => 'Eine Zertifizierung nach ISO 27001 ist damit möglich', 'is_correct' => true],
                    ['text' => 'Sie betrachtet nur einzelne kritische Geschäftsprozesse', 'is_correct' => false],
                    ['text' => 'Der Aufwand ist bei niedrigem Reifegrad geringer als bei der Basis-Absicherung', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Inhalte sollte die Sicherheitsleitlinie einer Institution mindestens enthalten?',
                'explanation' => 'Die Sicherheitsleitlinie sollte mindestens den Stellenwert der Informationssicherheit, den Bezug zu Geschäftszielen, Sicherheitsziele und Kernelemente der Sicherheitsstrategie, die Zusicherung der Durchsetzung durch die Institutionsleitung sowie die Beschreibung der Organisationsstruktur enthalten. Sie sollte kurz und bündig sein — nicht mehr als 20 Seiten.',
                'quote' => 'Die Sicherheitsleitlinie sollte kurz und bündig formuliert sein, da sich mehr als 20 Seiten in der Praxis nicht bewährt haben. Sie sollte jedoch mindestens die folgenden Informationen beinhalten: Stellenwert der Informationssicherheit und Bedeutung der wesentlichen Informationen [...] Sicherheitsziele und die Kernelemente der Sicherheitsstrategie [...] Zusicherung, dass die Sicherheitsleitlinie von der Institutionsleitung durchgesetzt wird',
                'source' => 'BSI-Standard 200-2, Kapitel 3.4.3, S. 33-34',
                'answers' => [
                    ['text' => 'Stellenwert der Informationssicherheit für die Institution', 'is_correct' => true],
                    ['text' => 'Sicherheitsziele und Kernelemente der Sicherheitsstrategie', 'is_correct' => true],
                    ['text' => 'Zusicherung der Durchsetzung durch die Institutionsleitung', 'is_correct' => true],
                    ['text' => 'Detaillierte technische Konfigurationsanweisungen für IT-Systeme', 'is_correct' => false],
                    ['text' => 'Vollständige Liste aller eingesetzten Sicherheitsprodukte', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'In welchem Intervall empfiehlt der BSI-Standard 200-2, die Sicherheitsleitlinie zu überprüfen?',
                'explanation' => 'Die Sicherheitsleitlinie sollte spätestens alle zwei Jahre erneut überprüft und gegebenenfalls angepasst werden. Dies wird aufgrund der häufig rasanten Entwicklungen im Bereich der IT und der Sicherheitslage empfohlen.',
                'quote' => 'Bei den häufig rasanten Entwicklungen im Bereich der IT einerseits und der Sicherheitslage andererseits empfiehlt es sich, die Sicherheitsleitlinie spätestens alle zwei Jahre erneut zu überdenken.',
                'source' => 'BSI-Standard 200-2, Kapitel 3.4.5, S. 35',
                'answers' => [
                    ['text' => 'Spätestens alle zwei Jahre', 'is_correct' => true],
                    ['text' => 'Jährlich', 'is_correct' => false],
                    ['text' => 'Alle fünf Jahre', 'is_correct' => false],
                    ['text' => 'Nur bei wesentlichen organisatorischen Änderungen', 'is_correct' => false],
                ],
            ],

            // === Kapitel 4: Organisation des Sicherheitsprozesses ===
            [
                'text' => 'Welche Grundregeln gelten bei der Definition von Rollen im Informationssicherheitsmanagement?',
                'explanation' => 'Die drei Grundregeln sind: Die Gesamtverantwortung verbleibt bei der Leitungsebene, es ist mindestens eine Person als ISB zu benennen, und jeder Mitarbeiter ist für die Informationssicherheit an seinem Arbeitsplatz und in seiner Umgebung verantwortlich. Der ISB hat Beratungsfunktion, nicht Weisungsbefugnis über Fachabteilungen.',
                'quote' => 'Die Gesamtverantwortung für die ordnungsgemäße und sichere Aufgabenerfüllung (und damit für die Informationssicherheit) verbleibt bei der Leitungsebene. Es ist mindestens eine Person (typischerweise als Informationssicherheitsbeauftragte(r)) zu benennen [...] Jeder Mitarbeiter ist gleichermaßen für seine originäre Aufgabe wie für die Aufrechterhaltung der Informationssicherheit an seinem Arbeitsplatz und in seiner Umgebung verantwortlich.',
                'source' => 'BSI-Standard 200-2, Kapitel 4, S. 36',
                'answers' => [
                    ['text' => 'Die Gesamtverantwortung verbleibt bei der Leitungsebene', 'is_correct' => true],
                    ['text' => 'Mindestens eine Person muss als ISB benannt werden', 'is_correct' => true],
                    ['text' => 'Jeder Mitarbeiter ist für IS an seinem Arbeitsplatz verantwortlich', 'is_correct' => true],
                    ['text' => 'Der ISB hat Weisungsbefugnis über alle Fachabteilungen', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Warum wurde die Bezeichnung "IT-Sicherheitsbeauftragter" (IT-SiBe) im IT-Grundschutz durch "Informationssicherheitsbeauftragter" (ISB) ersetzt?',
                'explanation' => 'Die Bezeichnung ISB ersetzt IT-SiBe, weil Informationssicherheit den umfangreichen Bereich des Schutzes von Informationen umfasst — nicht nur IT-bezogene Aspekte. Der Titel ISB macht deutlich, dass sich die Person um die Absicherung aller Arten von Informationen kümmert. Informationssicherheit sollte Teil des operationellen Risikomanagements sein.',
                'quote' => 'So macht der Titel des Informationssicherheitsbeauftragten statt des IT-Sicherheitsbeauftragten deutlich, dass diese Person sich um die Absicherung aller Arten von Informationen kümmert und nicht nur IT-bezogene Aspekte. Informationssicherheit sollte aber immer ein Teil des operationellen Risikomanagements einer Institution sein.',
                'source' => 'BSI-Standard 200-2, Kapitel 4.4, S. 40',
                'answers' => [
                    ['text' => 'Weil Informationssicherheit über IT-Sicherheit hinausgeht und alle Informationen umfasst', 'is_correct' => true],
                    ['text' => 'Weil dies eine EU-Verordnung vorschreibt', 'is_correct' => false],
                    ['text' => 'Weil die Aufgaben des ISB auf den IT-Betrieb beschränkt wurden', 'is_correct' => false],
                    ['text' => 'Weil der Titel IT-SiBe markenrechtlich geschützt ist', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Wo sollte der Informationssicherheitsbeauftragte (ISB) organisatorisch angesiedelt sein?',
                'explanation' => 'Es ist empfehlenswert, die Position des ISB direkt der obersten Leitungsebene zuzuordnen. Es ist davon abzuraten, den ISB in der IT-Abteilung zu verorten, da es hierbei zu Rollenkonflikten kommen kann — ein "aktiver" Administrator, der zusätzlich als ISB fungiert, hat einen inhärenten Aufgabenkonflikt.',
                'quote' => 'Es ist empfehlenswert, die Position des Informationssicherheitsbeauftragten direkt der obersten Leitungsebene zuzuordnen. Es ist davon abzuraten, den Sicherheitsbeauftragten in der IT-Abteilung zu verorten, da es hierbei zu Rollenkonflikten kommen kann.',
                'source' => 'BSI-Standard 200-2, Kapitel 4.4, S. 40, 42',
                'answers' => [
                    ['text' => 'Direkt bei der obersten Leitungsebene als Stabsstelle', 'is_correct' => true],
                    ['text' => 'In der IT-Abteilung als Teamleiter', 'is_correct' => false],
                    ['text' => 'Im Facility Management', 'is_correct' => false],
                    ['text' => 'Als Unterabteilung der Revision', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Aus wie vielen Personen besteht das IS-Management-Team im Extremfall mindestens?',
                'explanation' => 'Im Extremfall besteht das IS-Management-Team nur aus zwei Personen: dem Informationssicherheitsbeauftragten und seinem Stellvertreter. Die genaue Ausprägung hängt von der Größe der Institution, dem angestrebten Sicherheitsniveau und den vorhandenen Ressourcen ab.',
                'quote' => 'Im Extremfall besteht das IS-Management-Team nur aus zwei Personen, dem Informationssicherheitsbeauftragten, dem in diesem Fall sämtliche Aufgaben im Sicherheitsprozess obliegen, und seinem Stellvertreter.',
                'source' => 'BSI-Standard 200-2, Kapitel 4.5, S. 43',
                'answers' => [
                    ['text' => 'Zwei Personen (ISB und Stellvertreter)', 'is_correct' => true],
                    ['text' => 'Drei Personen', 'is_correct' => false],
                    ['text' => 'Fünf Personen', 'is_correct' => false],
                    ['text' => 'Eine Person (nur der ISB)', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussagen zur Personalunion von ISB und Datenschutzbeauftragtem sind korrekt?',
                'explanation' => 'Die beiden Rollen schließen sich nicht grundsätzlich aus, es sind allerdings einige Aspekte im Vorfeld zu klären: Die Schnittstellen zwischen den beiden Rollen sollten klar definiert und dokumentiert werden, auf beiden Seiten sollten direkte Berichtswege zur Leitungsebene existieren, und es muss sichergestellt sein, dass der ISB über ausreichend freie Ressourcen für die Wahrnehmung beider Rollen verfügt.',
                'quote' => 'Die beiden Rollen schließen sich nicht grundsätzlich aus, es sind allerdings einige Aspekte im Vorfeld zu klären: Die Schnittstellen zwischen den beiden Rollen sollten klar definiert und dokumentiert werden. Außerdem sollten auf beiden Seiten direkte Berichtswege zur Leitungsebene existieren.',
                'source' => 'BSI-Standard 200-2, Kapitel 4.4, S. 42-43',
                'answers' => [
                    ['text' => 'Eine Personalunion ist grundsätzlich möglich, aber nicht unproblematisch', 'is_correct' => true],
                    ['text' => 'Die Schnittstellen beider Rollen müssen klar definiert werden', 'is_correct' => true],
                    ['text' => 'Eine Personalunion ist gesetzlich verboten', 'is_correct' => false],
                    ['text' => 'Die Rollen haben keinerlei Überschneidungen', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was ist der IS-Koordinierungsausschuss und wie unterscheidet er sich von einer Dauereinrichtung?',
                'explanation' => 'Der IS-Koordinierungsausschuss ist in der Regel keine Dauereinrichtung, sondern wird bei Bedarf (z. B. zur Planung größerer Projekte) einberufen. Er koordiniert das Zusammenspiel zwischen dem IS-Management-Team, den Fachverantwortlichen, dem Sicherheitsbeauftragten und der Behörden- bzw. Unternehmensleitung.',
                'quote' => 'Der IS-Koordinierungsausschuss ist in der Regel keine Dauereinrichtung in einer Institution, sondern wird bei Bedarf (z. B. zur Planung größerer Projekte) einberufen. Er hat die Aufgabe, das Zusammenspiel zwischen dem IS-Management-Team, den Fachverantwortlichen, dem Sicherheitsbeauftragten und der Behörden- bzw. Unternehmensleitung zu koordinieren.',
                'source' => 'BSI-Standard 200-2, Kapitel 4.8, S. 46',
                'answers' => [
                    ['text' => 'Er ist keine Dauereinrichtung und wird bei Bedarf einberufen', 'is_correct' => true],
                    ['text' => 'Er koordiniert das Zusammenspiel zwischen IS-Management-Team und Leitungsebene', 'is_correct' => true],
                    ['text' => 'Er ist ein permanentes Gremium mit monatlichen Sitzungen', 'is_correct' => false],
                    ['text' => 'Er ersetzt das IS-Management-Team in kleinen Institutionen', 'is_correct' => false],
                ],
            ],

            // === Kapitel 5: Dokumentation im Sicherheitsprozess ===
            [
                'text' => 'Welche Klassifizierungsstufen werden im staatlichen Geheimschutz für die Vertraulichkeit verwendet?',
                'explanation' => 'Im staatlichen Geheimschutz werden die Stufen VS – NUR FÜR DEN DIENSTGEBRAUCH, VS – VERTRAULICH, GEHEIM und STRENG GEHEIM verwendet. "INTERN" ist eine Stufe, die in institutionseigenen Klassifizierungsschemata verwendet werden kann, aber nicht zum staatlichen Geheimschutz gehört.',
                'quote' => 'Ein typisches Beispiel für ein Klassifizierungsschema ist die im staatlichen Geheimschutz benutzte Einteilung in: VS – NUR FÜR DEN DIENSTGEBRAUCH, VS – VERTRAULICH, GEHEIM, STRENG GEHEIM',
                'source' => 'BSI-Standard 200-2, Kapitel 5.1, S. 54',
                'answers' => [
                    ['text' => 'VS – NUR FÜR DEN DIENSTGEBRAUCH', 'is_correct' => true],
                    ['text' => 'VS – VERTRAULICH', 'is_correct' => true],
                    ['text' => 'GEHEIM', 'is_correct' => true],
                    ['text' => 'STRENG GEHEIM', 'is_correct' => true],
                    ['text' => 'INTERN', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Mindestangaben müssen Dokumente des Sicherheitsmanagements enthalten?',
                'explanation' => 'Dokumente des Sicherheitsmanagements müssen mindestens enthalten: eindeutige Bezeichnung, Ersteller/Autor, Versionsnummer, letzte und nächste geplante Überarbeitung, Freigabedatum, Klassifizierung und berechtigte Rollen (Verteilerkreis). Die Anzahl der Druckexemplare ist keine Mindestangabe.',
                'quote' => 'Daher müssen mindestens folgende Angaben vorhanden sein: Eindeutige Bezeichnung (aussagekräftiger Titel), Ersteller / Autor / Dokumenteninhaber, Funktion des Erstellers, Versionsnummer, letzte Überarbeitung, nächste geplante Überarbeitung, freigegeben am / durch, Klassifizierung [...] und berechtigte Rollen (Verteilerkreis).',
                'source' => 'BSI-Standard 200-2, Kapitel 5.2.3, S. 57',
                'answers' => [
                    ['text' => 'Eindeutige Bezeichnung und Versionsnummer', 'is_correct' => true],
                    ['text' => 'Ersteller/Autor und Klassifizierung', 'is_correct' => true],
                    ['text' => 'Berechtigte Rollen (Verteilerkreis)', 'is_correct' => true],
                    ['text' => 'Anzahl der Druckexemplare', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welches Prinzip gilt für den Zugriff auf Dokumente des Sicherheitsmanagements?',
                'explanation' => 'Für den Zugriff auf Dokumente des Sicherheitsmanagements gilt das "Need-to-know-Prinzip" — der Zugriff sollte auf die Personen beschränkt werden, die die enthaltenen Informationen für ihre Tätigkeit benötigen. Eine sinnvolle Modularisierung der Dokumente ist daher empfehlenswert.',
                'quote' => 'Der Zugriff auf die Dokumente ist auf die Personen zu beschränken, die die enthaltenen Informationen für ihre Tätigkeit benötigen („Need-to-know-Prinzip"). Eine sinnvolle Modularisierung der Dokumente ist daher empfehlenswert.',
                'source' => 'BSI-Standard 200-2, Kapitel 5.2.3, S. 59',
                'answers' => [
                    ['text' => 'Need-to-know-Prinzip', 'is_correct' => true],
                    ['text' => 'Prinzip der maximalen Transparenz', 'is_correct' => false],
                    ['text' => 'Vier-Augen-Prinzip', 'is_correct' => false],
                    ['text' => 'Prinzip der offenen Verwaltung', 'is_correct' => false],
                ],
            ],

            // === Kapitel 6: Basis-Absicherung ===
            [
                'text' => 'Welche Aktionsfelder umfasst die Erstellung einer Sicherheitskonzeption nach Basis-Absicherung?',
                'explanation' => 'Die Basis-Absicherung gliedert sich in: Festlegung des Geltungsbereichs, Auswahl und Priorisierung relevanter Bausteine, IT-Grundschutz-Check (für Basisanforderungen) und Realisierung der Maßnahmen. Eine Risikoanalyse ist bei der Basis-Absicherung nicht vorgesehen — sie wird erst bei höherem Schutzbedarf in Standard- oder Kern-Absicherung durchgeführt.',
                'quote' => 'Die Erstellung einer Sicherheitskonzeption nach Basis-Absicherung gliedert sich in folgende Aktionsfelder, die anschließend noch näher vorgestellt werden sollen: Festlegung des Geltungsbereichs, Auswahl und Priorisierung, IT-Grundschutz-Check, Realisierung',
                'source' => 'BSI-Standard 200-2, Kapitel 6, S. 61',
                'answers' => [
                    ['text' => 'Festlegung des Geltungsbereichs', 'is_correct' => true],
                    ['text' => 'Auswahl und Priorisierung relevanter Bausteine', 'is_correct' => true],
                    ['text' => 'IT-Grundschutz-Check für Basisanforderungen', 'is_correct' => true],
                    ['text' => 'Realisierung der Maßnahmen', 'is_correct' => true],
                    ['text' => 'Durchführung einer Risikoanalyse', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Umsetzungsstatus sind beim IT-Grundschutz-Check möglich?',
                'explanation' => 'Beim Soll-Ist-Vergleich des IT-Grundschutz-Checks gibt es vier mögliche Umsetzungsstatus: "entbehrlich" (nicht relevant), "ja" (vollständig und angemessen umgesetzt), "teilweise" (nur teilweise umgesetzt) und "nein" (größtenteils noch nicht umgesetzt).',
                'quote' => '„entbehrlich" Die Erfüllung der Anforderung ist in der vorgeschlagenen Art nicht notwendig [...] „ja" Zu der Anforderung wurden geeignete Maßnahmen vollständig, wirksam und angemessen umgesetzt. „teilweise" Die Anforderung wurde nur teilweise umgesetzt. „nein" Die Anforderung wurde noch nicht erfüllt, also geeignete Maßnahmen sind größtenteils noch nicht umgesetzt.',
                'source' => 'BSI-Standard 200-2, Kapitel 6.3, S. 65',
                'answers' => [
                    ['text' => 'Entbehrlich', 'is_correct' => true],
                    ['text' => 'Ja', 'is_correct' => true],
                    ['text' => 'Teilweise', 'is_correct' => true],
                    ['text' => 'Nein', 'is_correct' => true],
                    ['text' => 'Geplant', 'is_correct' => false],
                ],
            ],

            // === Kapitel 7: Kern-Absicherung ===
            [
                'text' => 'Was sind "Kronjuwelen" im Kontext der Kern-Absicherung?',
                'explanation' => 'Als Kronjuwelen werden die Geschäftsprozesse und Informationen bezeichnet, die am wichtigsten für den Erhalt der Institution sind. Es handelt sich um Informationen und Geschäftsprozesse, nicht um Dienstleistungen, Anwendungen oder IT-Systeme. Der Schutzbedarf der Kronjuwelen ist mindestens als "hoch" einzuordnen.',
                'quote' => 'Als Kronjuwelen werden diejenigen Geschäftsprozesse und die Informationen bezeichnet, die am wichtigsten für den Erhalt der Institution sind. [...] Als Kronjuwelen werden Informationen oder Geschäftsprozesse bezeichnet, nicht Dienstleistungen, Anwendungen, IT-Systeme oder ähnliche Objekte.',
                'source' => 'BSI-Standard 200-2, Kapitel 7.3, S. 70',
                'answers' => [
                    ['text' => 'Die wichtigsten Geschäftsprozesse und Informationen für den Fortbestand der Institution', 'is_correct' => true],
                    ['text' => 'Alle IT-Systeme mit Internetanbindung', 'is_correct' => false],
                    ['text' => 'Ausschließlich die Server im Rechenzentrum', 'is_correct' => false],
                    ['text' => 'Die Geschäftsführung und deren direkte Mitarbeiter', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Charakteristika treffen auf Kronjuwelen zu?',
                'explanation' => 'Kronjuwelen ragen in ihrer Bedeutung deutlich aus der Masse heraus, ihr Schutzbedarf kann sich mit der Zeit verändern, und sie können auch in Formen vorliegen, die nicht auf den ersten Blick offensichtlich sind (z.B. handschriftliche Notizen, Wissen einzelner Mitarbeiter). Sie umfassen nicht automatisch alle IT-Systeme — nur wenige Assets haben deutlich erhöhten Schutzbedarf.',
                'quote' => 'Die Menge der Informationen und Geschäftsprozesse mit deutlich erhöhtem Schutzbedarf ist überschaubar bzw. umfasst nur einen kleinen Anteil aller Geschäftsprozesse der Institution. [...] Der Schutzbedarf von Kronjuwelen kann sich mit der Zeit verändern. [...] Kronjuwelen können auch in Formen vorliegen, die nicht auf den ersten Blick offensichtlich sind: dies mögen einzelne Dateien, Datensammlungen, strukturierte oder unstrukturierte Informationen bis hin zu handschriftlichen Notizen oder Gesprächen sein',
                'source' => 'BSI-Standard 200-2, Kapitel 7.3, S. 70-71',
                'answers' => [
                    ['text' => 'Sie umfassen nur einen kleinen Anteil aller Geschäftsprozesse', 'is_correct' => true],
                    ['text' => 'Ihr Schutzbedarf kann sich mit der Zeit verändern', 'is_correct' => true],
                    ['text' => 'Sie können auch nicht-digitale Formen annehmen, z.B. handschriftliche Notizen', 'is_correct' => true],
                    ['text' => 'Sie umfassen automatisch alle IT-Systeme der Institution', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Wer trifft die Entscheidung, welche Assets als Kronjuwelen eingestuft werden?',
                'explanation' => 'Die Festlegung, bei welchen Assets es sich um Kronjuwelen handelt, erfolgt typischerweise durch die Leitungsebene. Obwohl Fachverantwortliche, Sicherheitsbeauftragte und andere Instanzen vorschlagen können, muss die Entscheidung letztlich vonseiten der Leitungsebene erfolgen, da die Einstufung umfangreiche Schutzmaßnahmen nach sich zieht.',
                'quote' => 'Die Festlegung, bei welchen Assets es sich um Kronjuwelen handelt, erfolgt typischerweise durch die Leitungsebene. [...] Fachverantwortliche, Sicherheitsbeauftragte und andere Instanzen können vorschlagen, diese Informationen als Kronjuwelen einzustufen, die Entscheidung muss jedoch letztlich vonseiten der Leitungsebene erfolgen.',
                'source' => 'BSI-Standard 200-2, Kapitel 7.3, S. 71',
                'answers' => [
                    ['text' => 'Die Leitungsebene', 'is_correct' => true],
                    ['text' => 'Der Informationssicherheitsbeauftragte allein', 'is_correct' => false],
                    ['text' => 'Die IT-Abteilung', 'is_correct' => false],
                    ['text' => 'Externe Auditoren', 'is_correct' => false],
                ],
            ],

            // === Kapitel 8: Standard-Absicherung ===
            [
                'text' => 'Welche Teilaufgaben umfasst die Strukturanalyse bei der Standard-Absicherung?',
                'explanation' => 'Die Strukturanalyse gliedert sich in: Erfassung der zum Geltungsbereich zugehörigen Geschäftsprozesse, Anwendungen und Informationen; Netzplanerhebung; Erhebung von IT-, ICS- und IoT-Systemen und ähnlichen Objekten; sowie Erfassung der Räume und Gebäude. Die Schutzbedarfsfeststellung ist ein eigenständiger, nachfolgender Schritt.',
                'quote' => 'Die Strukturanalyse gliedert sich in folgende Teilaufgaben: Erfassung der zum Geltungsbereich zugehörigen Geschäftsprozesse, Anwendungen und Informationen, Netzplanerhebung, Erhebung von IT-, ICS- und IoT-Systemen und ähnlichen Objekten, Erfassung der Räume und Gebäude',
                'source' => 'BSI-Standard 200-2, Kapitel 8.1, S. 79',
                'answers' => [
                    ['text' => 'Erfassung der Geschäftsprozesse und zugehörigen Informationen', 'is_correct' => true],
                    ['text' => 'Netzplanerhebung', 'is_correct' => true],
                    ['text' => 'Erhebung von IT-, ICS- und IoT-Systemen', 'is_correct' => true],
                    ['text' => 'Erfassung der Räume und Gebäude', 'is_correct' => true],
                    ['text' => 'Schutzbedarfsfeststellung', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was ist das Ziel der Komplexitätsreduktion durch Gruppenbildung in der Strukturanalyse?',
                'explanation' => 'Ähnliche Objekte sollten sinnvoll zu Gruppen zusammengefasst werden, um die Ergebnisse der Strukturanalyse handhabbar zu machen. Objekte können gruppiert werden, wenn sie vom gleichen Typ sind, ähnliche Aufgaben haben, ähnlichen Rahmenbedingungen unterliegen und den gleichen Schutzbedarf aufweisen. Bei technischen Objekten kommt hinzu: ähnlich konfiguriert, im gleichen Netzsegment, ähnliche administrative Rahmenbedingungen.',
                'quote' => 'Ähnliche Objekte sollten deshalb sinnvoll zu Gruppen zusammengefasst werden. [...] Durch eine möglichst hohe Standardisierung innerhalb eines Informationsverbunds wird außerdem die Zahl potenzieller Sicherheitslücken reduziert und die Sicherheitsmaßnahmen für diesen Bereich können ohne Unterscheidung verschiedenster Schwachstellen umgesetzt werden.',
                'source' => 'BSI-Standard 200-2, Kapitel 8.1.1, S. 79-80',
                'answers' => [
                    ['text' => 'Die Datenmenge und Komplexität der Strukturanalyse handhabbar machen', 'is_correct' => true],
                    ['text' => 'Durch Standardisierung die Anzahl potenzieller Sicherheitslücken reduzieren', 'is_correct' => true],
                    ['text' => 'Jedes einzelne IT-System individuell zu betrachten', 'is_correct' => false],
                    ['text' => 'Die Anzahl der IT-Systeme physisch zu reduzieren', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was versteht man unter dem Maximumprinzip bei der Schutzbedarfsfeststellung?',
                'explanation' => 'Das Maximumprinzip bedeutet, dass der Schutzbedarf eines IT-Systems sich aus dem Maximum des Schutzbedarfs der darauf betriebenen Anwendungen ableitet. Wenn mehrere Anwendungen auf einem IT-System laufen, bestimmt die Anwendung mit dem höchsten Schutzbedarf den Gesamtschutzbedarf des Systems. Durch Kumulation mehrerer kleinerer Schäden kann sich der Schutzbedarf sogar noch erhöhen (Kumulationseffekt).',
                'quote' => 'Im Wesentlichen bestimmt der Schaden bzw. die Summe der Schäden mit den schwerwiegendsten Auswirkungen den Schutzbedarf eines Objektes (Maximumprinzip).',
                'source' => 'BSI-Standard 200-2, Kapitel 8.2.2, S. 108-109',
                'answers' => [
                    ['text' => 'Der höchste Schutzbedarf einer Anwendung bestimmt den Gesamtschutzbedarf des IT-Systems', 'is_correct' => true],
                    ['text' => 'Der niedrigste Schutzbedarf aller Anwendungen wird als Maßstab genommen', 'is_correct' => false],
                    ['text' => 'Der Durchschnitt aller Schutzbedarfswerte wird berechnet', 'is_correct' => false],
                    ['text' => 'Jede Anwendung wird unabhängig vom IT-System bewertet', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was beschreibt der Verteilungseffekt bei der Schutzbedarfsfeststellung?',
                'explanation' => 'Der Verteilungseffekt tritt hauptsächlich bezüglich des Grundwertes Verfügbarkeit auf. Er bedeutet, dass bei redundanter Auslegung von IT-Systemen der Schutzbedarf der Einzelkomponenten niedriger sein kann als der Schutzbedarf der Gesamtanwendung. Beispiel: Bei redundanten Ausweicharbeitsplätzen sinkt der Schutzbedarf bezüglich Verfügbarkeit, solange Ausweicharbeitsplätze zur Verfügung stehen.',
                'quote' => 'Hier ist der Schutzbedarf zu relativieren (Verteilungseffekt). Der Verteilungseffekt tritt hauptsächlich bezüglich des Grundwertes der Verfügbarkeit auf. So kann bei redundanter Auslegung von IT-Systemen der Schutzbedarf der Einzelkomponenten niedriger sein als der Schutzbedarf der Gesamtanwendung.',
                'source' => 'BSI-Standard 200-2, Kapitel 8.2.2, S. 109',
                'answers' => [
                    ['text' => 'Durch Redundanz kann der Schutzbedarf einzelner Komponenten sinken', 'is_correct' => true],
                    ['text' => 'Er tritt hauptsächlich bezüglich des Grundwertes Verfügbarkeit auf', 'is_correct' => true],
                    ['text' => 'Er bewirkt, dass sich der Schutzbedarf aller Systeme automatisch erhöht', 'is_correct' => false],
                    ['text' => 'Er ist nur auf die Vertraulichkeit anwendbar', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'In welchen Fällen muss zusätzlich zur Standard-Absicherung eine Risikoanalyse durchgeführt werden?',
                'explanation' => 'Eine Risikoanalyse muss durchgeführt werden, wenn der Informationsverbund Zielobjekte mit hohem oder sehr hohem Schutzbedarf enthält, wenn Zielobjekte nicht mit existierenden IT-Grundschutz-Bausteinen modelliert werden können, oder wenn Einsatzszenarien vorliegen, die im IT-Grundschutz nicht vorgesehen sind.',
                'quote' => 'In bestimmten Fällen muss jedoch eine explizite Risikoanalyse durchgeführt werden, beispielsweise wenn der betrachtete Informationsverbund Zielobjekte enthält, die einen hohen oder sehr hohen Schutzbedarf [...] haben oder mit den existierenden Bausteinen des IT-Grundschutzes nicht hinreichend abgebildet (modelliert) werden können oder in Einsatzszenarien (Umgebung, Anwendung) betrieben werden, die im Rahmen des IT-Grundschutzes nicht vorgesehen sind.',
                'source' => 'BSI-Standard 200-2, Kapitel 8.5, S. 152-153',
                'answers' => [
                    ['text' => 'Bei Zielobjekten mit hohem oder sehr hohem Schutzbedarf', 'is_correct' => true],
                    ['text' => 'Wenn Zielobjekte nicht mit IT-Grundschutz-Bausteinen modellierbar sind', 'is_correct' => true],
                    ['text' => 'Bei Einsatzszenarien, die im IT-Grundschutz nicht vorgesehen sind', 'is_correct' => true],
                    ['text' => 'Bei jedem Informationsverbund, unabhängig vom Schutzbedarf', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche sechs typischen Schadensszenarien werden bei der Schutzbedarfsfeststellung betrachtet?',
                'explanation' => 'Die sechs typischen Schadensszenarien sind: Verstoß gegen Gesetze/Vorschriften/Verträge, Beeinträchtigung des informationellen Selbstbestimmungsrechts, Beeinträchtigung der persönlichen Unversehrtheit, Beeinträchtigung der Aufgabenerfüllung, negative Innen- oder Außenwirkung und finanzielle Auswirkungen.',
                'quote' => 'Die Schäden, die bei dem Verlust der Vertraulichkeit, Integrität oder Verfügbarkeit für einen Geschäftsprozess bzw. eine Anwendung einschließlich ihrer Daten entstehen können, lassen sich typischerweise folgenden Schadensszenarien zuordnen: Verstoß gegen Gesetze/Vorschriften/Verträge, Beeinträchtigung des informationellen Selbstbestimmungsrechts, Beeinträchtigung der persönlichen Unversehrtheit, Beeinträchtigung der Aufgabenerfüllung, negative Innen- oder Außenwirkung und finanzielle Auswirkungen.',
                'source' => 'BSI-Standard 200-2, Kapitel 8.2.1, S. 105',
                'answers' => [
                    ['text' => 'Verstoß gegen Gesetze/Vorschriften/Verträge', 'is_correct' => true],
                    ['text' => 'Beeinträchtigung des informationellen Selbstbestimmungsrechts', 'is_correct' => true],
                    ['text' => 'Beeinträchtigung der Aufgabenerfüllung', 'is_correct' => true],
                    ['text' => 'Negative Innen- oder Außenwirkung', 'is_correct' => true],
                    ['text' => 'Finanzielle Auswirkungen', 'is_correct' => true],
                    ['text' => 'Verlust des Marktanteils', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was kennzeichnet die Schutzbedarfskategorie "sehr hoch" hinsichtlich der Aufgabenerfüllung?',
                'explanation' => 'Bei "sehr hoch" wird die Beeinträchtigung von allen Betroffenen als nicht tolerabel eingeschätzt und die maximal tolerierbare Ausfallzeit ist kleiner als eine Stunde. Bei "hoch" liegt die tolerierbare Ausfallzeit zwischen einer und 24 Stunden, bei "normal" zwischen 24 und 72 Stunden.',
                'quote' => 'Schutzbedarfskategorie „sehr hoch", Beeinträchtigung der Aufgabenerfüllung: Die Beeinträchtigung würde von allen Betroffenen als nicht tolerabel eingeschätzt werden. Die maximal tolerierbare Ausfallzeit ist kleiner als eine Stunde.',
                'source' => 'BSI-Standard 200-2, Kapitel 8.2.1, S. 107',
                'answers' => [
                    ['text' => 'Die Beeinträchtigung wird von allen Betroffenen als nicht tolerabel eingeschätzt', 'is_correct' => true],
                    ['text' => 'Die maximal tolerierbare Ausfallzeit ist kleiner als eine Stunde', 'is_correct' => true],
                    ['text' => 'Die maximal tolerierbare Ausfallzeit liegt zwischen 24 und 72 Stunden', 'is_correct' => false],
                    ['text' => 'Kleinere Fehler können toleriert werden', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche drei Kategorien von Sicherheitsanforderungen unterscheidet das IT-Grundschutz-Kompendium in seinen Bausteinen?',
                'explanation' => 'Die Anforderungen in den Bausteinen sind in drei Kategorien unterteilt: Basis-Anforderungen (müssen vorrangig erfüllt werden), Standard-Anforderungen (adressieren normalen Schutzbedarf, sollten grundsätzlich erfüllt werden) und Anforderungen bei erhöhtem Schutzbedarf (Vorschläge für weiterführende Absicherung).',
                'quote' => 'Basis-Anforderungen müssen vorrangig erfüllt werden, da bei diesen Empfehlungen mit (relativ) geringem Aufwand der größtmögliche Nutzen erzielt werden kann. [...] Standard-Anforderungen bauen auf den Basis-Anforderungen auf und adressieren den normalen Schutzbedarf. [...] Anforderungen bei erhöhtem Schutzbedarf sind eine Auswahl von Vorschlägen für eine weiterführende Absicherung',
                'source' => 'BSI-Standard 200-2, Kapitel 8.3.1, S. 133-134',
                'answers' => [
                    ['text' => 'Basis-Anforderungen', 'is_correct' => true],
                    ['text' => 'Standard-Anforderungen', 'is_correct' => true],
                    ['text' => 'Anforderungen bei erhöhtem Schutzbedarf', 'is_correct' => true],
                    ['text' => 'Kritische Anforderungen', 'is_correct' => false],
                    ['text' => 'Optionale Anforderungen', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'In welcher Reihenfolge (R1, R2, R3) sollten die IT-Grundschutz-Bausteine umgesetzt werden?',
                'explanation' => 'R1-Bausteine sollten vorrangig umgesetzt werden, da sie die Grundlage für einen effektiven Sicherheitsprozess bilden (ISMS, ORP, OPS.1.1). R2-Bausteine sind als Nächstes umzusetzen, da sie für nachhaltige Sicherheit erforderlich sind. R3-Bausteine werden zur Erreichung des angestrebten Sicherheitsniveaus benötigt, aber erst nach den anderen Bausteinen betrachtet.',
                'quote' => 'R1: Diese Bausteine sollten vorrangig umgesetzt werden, da sie die Grundlage für einen effektiven Sicherheitsprozess bilden. R2: Diese Bausteine sollten als Nächstes umgesetzt werden, da sie in wesentlichen Teilen des Informationsverbunds für nachhaltige Sicherheit erforderlich sind. R3: Diese Bausteine werden zur Erreichung des angestrebten Sicherheitsniveaus ebenfalls benötigt [...] Mit R1 sind die Bausteine gekennzeichnet [...] der Bereiche ISMS Managementsysteme für Informationssicherheit, ORP Organisation und Personal, OPS.1.1 Kern-IT-Betrieb.',
                'source' => 'BSI-Standard 200-2, Kapitel 8.3.3, S. 137-138',
                'answers' => [
                    ['text' => 'R1: Vorrangig – bildet Grundlage (ISMS, ORP, OPS.1.1)', 'is_correct' => true],
                    ['text' => 'R2: Als Nächstes – für nachhaltige Sicherheit erforderlich', 'is_correct' => true],
                    ['text' => 'R3: Wird benötigt, aber erst nach R1 und R2', 'is_correct' => true],
                    ['text' => 'R1 und R3 sind gleichwertig und können parallel umgesetzt werden', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aspekte müssen bei der Angemessenheit von Sicherheitsmaßnahmen berücksichtigt werden?',
                'explanation' => 'Sicherheitsmaßnahmen müssen angemessen sein hinsichtlich: Wirksamkeit (Effektivität), Eignung (praktisch umsetzbar), Praktikabilität (leicht verständlich, wenig fehleranfällig), Akzeptanz (barrierefrei, nicht diskriminierend) und Wirtschaftlichkeit (Kosten in geeignetem Verhältnis zum Schutzwert).',
                'quote' => 'Angemessen bedeutet: Wirksamkeit (Effektivität): Sie müssen vor den möglichen Gefährdungen wirksam schützen [...] Eignung: Sie müssen in der Praxis tatsächlich umsetzbar sein [...] Praktikabilität: Sie sollen leicht verständlich, einfach anzuwenden und wenig fehleranfällig sein. Akzeptanz: Sie müssen für alle Benutzer anwendbar (barrierefrei) sein und dürfen niemanden diskriminieren oder beeinträchtigen. Wirtschaftlichkeit: Mit den eingesetzten Mitteln sollte ein möglichst gutes Ergebnis erreicht werden.',
                'source' => 'BSI-Standard 200-2, Kapitel 8.3.6, S. 143-144',
                'answers' => [
                    ['text' => 'Wirksamkeit (Effektivität)', 'is_correct' => true],
                    ['text' => 'Eignung und Praktikabilität', 'is_correct' => true],
                    ['text' => 'Akzeptanz und Wirtschaftlichkeit', 'is_correct' => true],
                    ['text' => 'Ausschließlich die Beschaffungskosten', 'is_correct' => false],
                ],
            ],

            // === Kapitel 9: Umsetzung der Sicherheitskonzeption ===
            [
                'text' => 'Welche Aussagen zum Umgang mit Basis-Anforderungen bei der Umsetzung sind korrekt?',
                'explanation' => 'Basis-Anforderungen müssen im Normalfall immer erfüllt werden. Die Akzeptanz eines Restrisikos ist aufgrund ihrer elementaren Natur nicht vorgesehen. Wenn keine angemessene Maßnahme gefunden werden kann, muss die Entscheidung dokumentiert und das Restrisiko als solches behandelt werden.',
                'quote' => 'Basis-Anforderungen müssen im Normalfall immer erfüllt werden, die Akzeptanz eines Restrisikos ist aufgrund ihrer elementaren Natur nicht vorgesehen. Falls keine angemessene Maßnahme gefunden werden kann, muss die Entscheidung dokumentiert und das Restrisiko als solches behandelt werden.',
                'source' => 'BSI-Standard 200-2, Kapitel 9.2, S. 159',
                'answers' => [
                    ['text' => 'Basis-Anforderungen müssen im Normalfall immer erfüllt werden', 'is_correct' => true],
                    ['text' => 'Die Akzeptanz eines Restrisikos ist bei Basis-Anforderungen nicht vorgesehen', 'is_correct' => true],
                    ['text' => 'Basis-Anforderungen können nach Belieben priorisiert und verschoben werden', 'is_correct' => false],
                    ['text' => 'Basis-Anforderungen gelten nur für IT-Systeme mit hohem Schutzbedarf', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was sind realisierungsbegleitende Maßnahmen bei der Umsetzung der Sicherheitskonzeption?',
                'explanation' => 'Realisierungsbegleitende Maßnahmen umfassen insbesondere Sensibilisierungsmaßnahmen, die darauf abzielen, den betroffenen Mitarbeitern die Belange der Informationssicherheit zu verdeutlichen. Darüber hinaus müssen die Mitarbeiter geschult werden, um die neuen Sicherheitsmaßnahmen korrekt umzusetzen. Ohne Schulung werden Maßnahmen oft nicht umgesetzt oder verlieren ihre Wirkung.',
                'quote' => 'Zu diesen Maßnahmen gehören insbesondere Sensibilisierungsmaßnahmen, die darauf abzielen, die Belange der Informationssicherheit zu verdeutlichen und die von neuen Sicherheitsmaßnahmen betroffenen Mitarbeiter über die Notwendigkeit und die Konsequenzen der Maßnahmen zu unterrichten. Darüber hinaus müssen die betroffenen Mitarbeiter geschult werden, die neuen Sicherheitsmaßnahmen korrekt um- und einzusetzen.',
                'source' => 'BSI-Standard 200-2, Kapitel 9.5, S. 162',
                'answers' => [
                    ['text' => 'Sensibilisierungsmaßnahmen für betroffene Mitarbeiter', 'is_correct' => true],
                    ['text' => 'Schulung der Mitarbeiter für neue Sicherheitsmaßnahmen', 'is_correct' => true],
                    ['text' => 'Automatische Installation von Sicherheitssoftware auf allen Endgeräten', 'is_correct' => false],
                    ['text' => 'Austausch aller vorhandenen IT-Systeme', 'is_correct' => false],
                ],
            ],

            // === Kapitel 10: Aufrechterhaltung und Verbesserung ===
            [
                'text' => 'Welche Reifegrade definiert der BSI-Standard 200-2 für die Bewertung eines ISMS?',
                'explanation' => 'Das Reifegradmodell umfasst 6 Stufen (0-5): 0 = kein ISMS existiert und nichts geplant, 1 = ISMS geplant aber nicht etabliert, 2 = ISMS zum Teil etabliert, 3 = ISMS voll etabliert und dokumentiert, 4 = zusätzlich regelmäßige Effektivitätsprüfung, 5 = zusätzlich regelmäßige Verbesserung.',
                'quote' => '0: Es existiert kein ISMS und es ist auch nichts geplant. 1: ISMS ist geplant, aber nicht etabliert. 2: ISMS ist zum Teil etabliert. 3: ISMS ist voll etabliert und dokumentiert. 4: Zusätzlich zum Reifegrad 3 wird das ISMS regelmäßig auf Effektivität überprüft. 5: Zusätzlich zum Reifegrad 4 wird das ISMS regelmäßig verbessert.',
                'source' => 'BSI-Standard 200-2, Kapitel 10.1.2, S. 165-166',
                'answers' => [
                    ['text' => '0 – Kein ISMS, nichts geplant', 'is_correct' => true],
                    ['text' => '3 – ISMS voll etabliert und dokumentiert', 'is_correct' => true],
                    ['text' => '5 – ISMS wird regelmäßig verbessert', 'is_correct' => true],
                    ['text' => '7 – Weltklasse-Sicherheitsniveau erreicht', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Kennzahlen können zur Überprüfung der Informationssicherheit herangezogen werden?',
                'explanation' => 'Typische Kennzahlen sind: Anzahl der erkannten Schadsoftware-Muster, Anzahl der installierten Sicherheitspatches, Dauer der Systemausfälle und Anzahl der durchgeführten Sicherheitsschulungen. Kennzahlen sind generell nur sinnvoll, wenn vorab klar ist, welches Ziel mit der Messung verfolgt wird.',
                'quote' => 'Dies betrifft im Allgemeinen die technische Sicherheit, bei der über Sensoren automatisiert Messwerte zurückgemeldet werden können, und andere, leicht quantifizierbare Aussagen, wie z. B. Anzahl der erkannten Schadsoftware-Muster, Anzahl der installierten Sicherheitspatches, Dauer der Systemausfälle, Anzahl der durchgeführten Sicherheitsschulungen.',
                'source' => 'BSI-Standard 200-2, Kapitel 10.1.1, S. 165',
                'answers' => [
                    ['text' => 'Anzahl der erkannten Schadsoftware-Muster', 'is_correct' => true],
                    ['text' => 'Anzahl der installierten Sicherheitspatches', 'is_correct' => true],
                    ['text' => 'Dauer der Systemausfälle', 'is_correct' => true],
                    ['text' => 'Anzahl der Mitarbeiter in der IT-Abteilung', 'is_correct' => false],
                ],
            ],

            // === Kapitel 11: Zertifizierung ===
            [
                'text' => 'Welche Vorgehensweisen des IT-Grundschutzes ermöglichen grundsätzlich eine ISO 27001-Zertifizierung auf Basis von IT-Grundschutz?',
                'explanation' => 'Eine ISO 27001-Zertifizierung auf Basis von IT-Grundschutz ist für die Standard-Absicherung vorgesehen sowie für die Kern-Absicherung grundsätzlich möglich. Bei einer reinen Basis-Absicherung reichen die umgesetzten Sicherheitsmaßnahmen für eine Zertifizierung nicht aus, sie kann aber als Einstieg für eine der anderen Vorgehensweisen dienen.',
                'quote' => 'Eine solche Zertifizierung ist für die Standard-Absicherung vorgesehen sowie für die Kern-Absicherung grundsätzlich möglich. Bei einer reinen Basis-Absicherung reichen die umgesetzten Sicherheitsmaßnahmen für eine Zertifizierung nicht aus, können aber als Einstieg für eine der anderen beiden Vorgehensweisen dienen.',
                'source' => 'BSI-Standard 200-2, Kapitel 11, S. 171',
                'answers' => [
                    ['text' => 'Standard-Absicherung', 'is_correct' => true],
                    ['text' => 'Kern-Absicherung', 'is_correct' => true],
                    ['text' => 'Basis-Absicherung', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussagen zur ISO 27001-Zertifizierung auf Basis von IT-Grundschutz sind korrekt?',
                'explanation' => 'Grundlage für die Zertifizierung ist die Durchführung eines Audits durch einen externen, beim BSI zertifizierten Auditor. Das Ergebnis ist ein Auditbericht, der der Zertifizierungsstelle vorgelegt wird. Kriterienwerke des Verfahrens sind neben ISO 27001 die IT-Grundschutz-Vorgehensweise und das IT-Grundschutz-Kompendium des BSI.',
                'quote' => 'Grundlage für die Vergabe eines ISO 27001-Zertifikats auf der Basis von IT-Grundschutz ist die Durchführung eines Audits durch einen externen, beim BSI zertifizierten Auditor. Das Ergebnis des Audits ist ein Auditbericht, der der Zertifizierungsstelle vorgelegt wird. Kriterienwerke des Verfahrens sind neben der Norm ISO 27001 die in diesem Dokument beschriebene IT-Grundschutz-Vorgehensweise und das IT-Grundschutz Kompendium des BSI.',
                'source' => 'BSI-Standard 200-2, Kapitel 11, S. 171',
                'answers' => [
                    ['text' => 'Ein externer, beim BSI zertifizierter Auditor führt das Audit durch', 'is_correct' => true],
                    ['text' => 'Kriterienwerke sind ISO 27001, IT-Grundschutz-Vorgehensweise und Kompendium', 'is_correct' => true],
                    ['text' => 'Die Zertifizierung wird direkt vom BSI durchgeführt', 'is_correct' => false],
                    ['text' => 'Ein internes Audit durch den ISB ist ausreichend', 'is_correct' => false],
                ],
            ],

            // === Übergreifende Fragen ===
            [
                'text' => 'Was beschreibt der Begriff "Informationsverbund" im IT-Grundschutz?',
                'explanation' => 'Ein Informationsverbund umfasst die Gesamtheit von infrastrukturellen, organisatorischen, personellen und technischen Komponenten, die der Aufgabenerfüllung in einem bestimmten Anwendungsbereich der Informationsverarbeitung dienen. Er kann die gesamte Informationsverarbeitung einer Institution umfassen oder einzelne Bereiche, die nach organisatorischen oder technischen Strukturen gegliedert sind.',
                'quote' => 'Ein Informationsverbund umfasst die Gesamtheit von infrastrukturellen, organisatorischen, personellen und technischen Komponenten, die der Aufgabenerfüllung in einem bestimmten Anwendungsbereich der Informationsverarbeitung dienen.',
                'source' => 'BSI-Standard 200-2, Kapitel 3.3.4, S. 30',
                'answers' => [
                    ['text' => 'Gesamtheit der infrastrukturellen, organisatorischen, personellen und technischen Komponenten für die Informationsverarbeitung', 'is_correct' => true],
                    ['text' => 'Ausschließlich das physische Netzwerk einer Institution', 'is_correct' => false],
                    ['text' => 'Die Zusammenfassung aller IT-Geräte in einem Gebäude', 'is_correct' => false],
                    ['text' => 'Ein Verbund mehrerer Institutionen für gemeinsame IT-Nutzung', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Optionen gibt es für die Behandlung von Risiken im IT-Grundschutz?',
                'explanation' => 'Es gibt drei Optionen zur Behandlung von Risiken: Risiken können vermieden werden (z.B. durch Umstrukturierung von Geschäftsprozessen), Risiken können durch Sicherheitsmaßnahmen reduziert werden, und Risiken können transferiert werden (z.B. durch Outsourcing oder Versicherungen). Nach der Risikobehandlung verbleibt ein Restrisiko, das dokumentiert und der Leitungsebene zur Risiko-Akzeptanz vorgelegt werden muss.',
                'quote' => 'Es gibt folgende Optionen zur Behandlung von Risiken: Risiken können vermieden werden (z. B. durch Umstrukturierung von Geschäftsprozessen oder des Informationsverbunds). Risiken können durch entsprechende Sicherheitsmaßnahmen reduziert werden. Risiken können transferiert werden (z. B. durch Outsourcing oder Versicherungen).',
                'source' => 'BSI-Standard 200-2, Kapitel 8.5, S. 154',
                'answers' => [
                    ['text' => 'Risiken vermeiden (z.B. durch Umstrukturierung)', 'is_correct' => true],
                    ['text' => 'Risiken durch Sicherheitsmaßnahmen reduzieren', 'is_correct' => true],
                    ['text' => 'Risiken transferieren (z.B. durch Versicherungen)', 'is_correct' => true],
                    ['text' => 'Risiken ignorieren und nicht dokumentieren', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Rolle spielt der ICS-Informationssicherheitsbeauftragte (ICS-ISB)?',
                'explanation' => 'Der ICS-ISB ist verantwortlich für die Umsetzung von Anforderungen der Informationssicherheit im Bereich industrieller Steuerungskomponenten (ICS). Er sollte Mitglied im IS-Management-Team und im IS-Koordinierungsausschuss sein. Er muss eng mit dem ISB kooperieren und Sicherheitsrichtlinien unter Einbeziehung von Safety und Security erstellen.',
                'quote' => 'Um die speziellen Anforderungen im Bereich der industriellen Steuerung abzudecken und um die Sicherheitsorganisation aus dem Bereich der industriellen Steuerung in das Gesamt-ISMS einzubinden, sollte die Institution einen ICS-Informationssicherheitsbeauftragten (ICS-ISB) benennen. Dieser sollte Mitglied im IS-Management-Team sein. [...] eng mit dem Informationssicherheitsbeauftragten kooperieren',
                'source' => 'BSI-Standard 200-2, Kapitel 4.7, S. 45-46',
                'answers' => [
                    ['text' => 'Er ist für Informationssicherheit im Bereich industrieller Steuerung (ICS) verantwortlich', 'is_correct' => true],
                    ['text' => 'Er sollte Mitglied im IS-Management-Team sein', 'is_correct' => true],
                    ['text' => 'Er muss eng mit dem ISB kooperieren', 'is_correct' => true],
                    ['text' => 'Er ersetzt den ISB in Produktionsunternehmen', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Schutzwirkung haben die Sicherheitsanforderungen nach IT-Grundschutz je nach Schutzbedarfskategorie?',
                'explanation' => 'Bei normalem Schutzbedarf sind die IT-Grundschutz-Anforderungen im Allgemeinen ausreichend und angemessen. Bei hohem Schutzbedarf liefern sie eine Standard-Absicherung, die aber unter Umständen alleine nicht ausreicht — weitergehende Maßnahmen auf Basis einer Risikoanalyse sind zu ermitteln. Bei sehr hohem Schutzbedarf reichen die Standard-Anforderungen alleine im Allgemeinen nicht aus — individuelle zusätzliche Maßnahmen müssen durch Risikoanalyse ermittelt werden.',
                'quote' => 'Schutzbedarfskategorie „normal": Sicherheitsanforderungen nach IT-Grundschutz sind im Allgemeinen ausreichend und angemessen. Schutzbedarfskategorie „hoch": Sicherheitsanforderungen nach IT-Grundschutz liefern eine Standard-Absicherung, sind aber unter Umständen alleine nicht ausreichend. Weitergehende Maßnahmen sollten auf Basis einer Risikoanalyse ermittelt werden. Schutzbedarfskategorie „sehr hoch": [...] reichen aber alleine im Allgemeinen nicht aus. Die erforderlichen zusätzlichen Sicherheitsmaßnahmen müssen individuell auf der Grundlage einer Risikoanalyse ermittelt werden.',
                'source' => 'BSI-Standard 200-2, Kapitel 8.2.9, S. 130',
                'answers' => [
                    ['text' => 'Normal: IT-Grundschutz-Anforderungen sind ausreichend und angemessen', 'is_correct' => true],
                    ['text' => 'Hoch: Standard-Absicherung reicht unter Umständen nicht, Risikoanalyse nötig', 'is_correct' => true],
                    ['text' => 'Sehr hoch: Individuelle Maßnahmen durch Risikoanalyse zwingend erforderlich', 'is_correct' => true],
                    ['text' => 'Hoch und sehr hoch: IT-Grundschutz ist nicht anwendbar', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was versteht man unter dem Kumulationseffekt bei der Schutzbedarfsfeststellung?',
                'explanation' => 'Der Kumulationseffekt beschreibt, dass sich durch die Kumulation mehrerer (z.B. kleinerer) Schäden auf einem IT-System ein insgesamt höherer Gesamtschaden ergeben kann. Beispiel: Wenn auf einem Server viele Anwendungen mit normalem Schutzbedarf laufen, kann sich der Schutzbedarf des Servers durch Kumulation auf "hoch" erhöhen, weil der Ausfall des Servers alle Anwendungen gleichzeitig betrifft.',
                'quote' => 'Werden mehrere Anwendungen bzw. Informationen auf einem IT-System (oder in einem Raum oder über eine Kommunikationsverbindung) verarbeitet, so ist zu überlegen, ob durch Kumulation mehrerer (z. B. kleinerer) Schäden auf einem IT-System ein insgesamt höherer Gesamtschaden entstehen kann. Dann erhöht sich der Schutzbedarf des Objektes, also hier des IT-Systems, entsprechend (Kumulationseffekt).',
                'source' => 'BSI-Standard 200-2, Kapitel 8.2.2, S. 109',
                'answers' => [
                    ['text' => 'Durch Bündelung mehrerer Schäden kann ein höherer Gesamtschaden entstehen', 'is_correct' => true],
                    ['text' => 'Der Schutzbedarf eines Systems kann sich dadurch erhöhen', 'is_correct' => true],
                    ['text' => 'Er führt dazu, dass sich der Schutzbedarf aller Systeme automatisch verringert', 'is_correct' => false],
                    ['text' => 'Er beschreibt die Verteilung von Schutzbedarf durch Redundanz', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Informationen sollte ein Realisierungsplan für Sicherheitsmaßnahmen mindestens enthalten?',
                'explanation' => 'Der Realisierungsplan sollte mindestens enthalten: Bezeichnung des Zielobjektes, Nummer/Titel des betrachteten Bausteins, Beschreibung der zu erfüllenden Anforderung, Beschreibung der umzusetzenden Maßnahme, Terminplanung und Budgetplanung sowie den Verantwortlichen für die Umsetzung.',
                'quote' => 'Der Realisierungsplan sollte mindestens folgende Informationen umfassen: Bezeichnung des Zielobjektes als Einsatzumfeld, Nummer bzw. Titel des betrachteten Bausteins, Titel bzw. Beschreibung der zu erfüllenden Anforderung, Beschreibung der umzusetzenden Maßnahme [...] Terminplanung für die Umsetzung, Budgetplanung [...] Verantwortliche für die Umsetzung von Maßnahmen.',
                'source' => 'BSI-Standard 200-2, Kapitel 9.4, S. 161-162',
                'answers' => [
                    ['text' => 'Bezeichnung des Zielobjektes und betrachteten Bausteins', 'is_correct' => true],
                    ['text' => 'Beschreibung der umzusetzenden Maßnahme', 'is_correct' => true],
                    ['text' => 'Terminplanung, Budgetplanung und Verantwortlicher', 'is_correct' => true],
                    ['text' => 'Vollständiger Lebenslauf des verantwortlichen Mitarbeiters', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Unter welchen Bedingungen darf der Umsetzungsstatus einer Anforderung auf "entbehrlich" gesetzt werden?',
                'explanation' => 'Der Status "entbehrlich" darf nur gesetzt werden, wenn die Erfüllung der Anforderung in der vorgeschlagenen Art nicht notwendig ist, weil die Anforderung im betrachteten Informationsverbund nicht relevant ist (z.B. weil Dienste nicht aktiviert wurden) oder durch Alternativmaßnahmen behandelt wurde. Anforderungen dürfen NICHT auf "entbehrlich" gesetzt werden, wenn das Risiko pauschal akzeptiert oder über die Kreuzreferenztabelle ausgeschlossen wird. Bei Basis-Anforderungen ist besonders zu beachten, dass das entstehende Risiko nicht übernommen werden kann.',
                'quote' => '„entbehrlich" Die Erfüllung der Anforderung ist in der vorgeschlagenen Art nicht notwendig, weil die Anforderung im betrachteten Informationsverbund nicht relevant ist (z. B. weil Dienste nicht aktiviert wurden) oder durch Alternativmaßnahmen behandelt wurde. [...] Anforderungen dürfen nicht auf „entbehrlich" gesetzt werden, wenn das Risiko für eine im Baustein identifizierte elementare Gefährdung über die Kreuzreferenztabelle pauschal akzeptiert oder ausgeschlossen wird.',
                'source' => 'BSI-Standard 200-2, Kapitel 8.4.2, S. 150',
                'answers' => [
                    ['text' => 'Wenn die Anforderung im betrachteten Informationsverbund nicht relevant ist', 'is_correct' => true],
                    ['text' => 'Wenn die Anforderung durch Alternativmaßnahmen erfüllt wird', 'is_correct' => true],
                    ['text' => 'Wenn die Umsetzung zu teuer erscheint', 'is_correct' => false],
                    ['text' => 'Wenn das Risiko pauschal akzeptiert wird', 'is_correct' => false],
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
