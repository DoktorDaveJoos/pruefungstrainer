<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Database\Seeder;

class ItGrundschutzKompendiumQuestionsSeeder extends Seeder
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
            // === IT-Grundschutz - Basis für Informationssicherheit ===
            [
                'text' => 'Welche potenziellen Schäden können laut IT-Grundschutz-Kompendium durch Mängel im Bereich der Informationssicherheit entstehen?',
                'explanation' => 'Das Kompendium nennt drei Hauptkategorien potenzieller Schäden: Verlust der Verfügbarkeit, Verlust der Vertraulichkeit und Verlust der Integrität (Korrektheit) von Informationen. Dies sind die drei klassischen Grundwerte der Informationssicherheit.',
                'quote' => 'Mängel im Bereich der Informationssicherheit können zu erheblichen Problemen führen. Die potenziellen Schäden lassen sich verschiedenen Kategorien zuordnen: Verlust der Verfügbarkeit [...] Verlust der Vertraulichkeit von Informationen [...] Verlust der Integrität (Korrektheit) von Informationen',
                'source' => 'IT-Grundschutz-Kompendium, IT-Grundschutz – Basis für Informationssicherheit, S. 1',
                'answers' => [
                    ['text' => 'Verlust der Verfügbarkeit', 'is_correct' => true],
                    ['text' => 'Verlust der Vertraulichkeit', 'is_correct' => true],
                    ['text' => 'Verlust der Integrität (Korrektheit)', 'is_correct' => true],
                    ['text' => 'Verlust der Authentizität', 'is_correct' => false],
                    ['text' => 'Verlust der Nichtabstreitbarkeit', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussage zum IT-Grundschutz ist NICHT korrekt?',
                'explanation' => 'IT-Grundschutz setzt voraus, dass eine Organisationseinheit (IT-Betrieb) existiert, die die interne IT einrichtet, betreibt, überwacht und wartet. Er richtet sich nicht ausschließlich an Großunternehmen, sondern kann sowohl von KMU als auch von großen Institutionen eingesetzt werden.',
                'quote' => 'IT-Grundschutz kann sowohl von kleinen und mittleren (KMU) als auch großen Institutionen zum Aufbau eines Managementsystems für Informationssicherheit eingesetzt werden. Dabei setzt eine erfolgreiche Umsetzung des IT-Grundschutz-Kompendiums jedoch voraus, dass eine Organisationseinheit (IT-Betrieb) etabliert wird, die die interne IT einrichtet, betreibt, überwacht und wartet.',
                'source' => 'IT-Grundschutz-Kompendium, IT-Grundschutz – Basis für Informationssicherheit, S. 1',
                'answers' => [
                    ['text' => 'IT-Grundschutz richtet sich ausschließlich an Großunternehmen und Behörden', 'is_correct' => true],
                    ['text' => 'IT-Grundschutz setzt eine etablierte Organisationseinheit (IT-Betrieb) voraus', 'is_correct' => false],
                    ['text' => 'IT-Grundschutz kann von KMU eingesetzt werden', 'is_correct' => false],
                    ['text' => 'IT-Grundschutz dient dem Aufbau eines ISMS', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was bedeutet das Modalverb "MUSS" in den Anforderungen des IT-Grundschutz-Kompendiums?',
                'explanation' => 'MUSS/DARF NUR bedeutet, dass es sich um eine Anforderung handelt, die unbedingt erfüllt werden muss und für die keine Risikoübernahme möglich ist. Im Gegensatz dazu bedeutet SOLLTE, dass die Anforderung normalerweise erfüllt werden muss, aber Gründe für eine Abweichung existieren können.',
                'quote' => 'MUSS / DARF NUR: Dieser Ausdruck bedeutet, dass es sich um eine Anforderung handelt, die unbedingt erfüllt werden muss (uneingeschränkte Anforderungen, für die keine Risikoübernahme möglich ist).',
                'source' => 'IT-Grundschutz-Kompendium, IT-Grundschutz – Basis für Informationssicherheit, S. 6',
                'answers' => [
                    ['text' => 'Eine uneingeschränkte Anforderung, für die keine Risikoübernahme möglich ist', 'is_correct' => true],
                    ['text' => 'Eine Empfehlung, die nach Möglichkeit umgesetzt werden sollte', 'is_correct' => false],
                    ['text' => 'Eine optionale Anforderung bei erhöhtem Schutzbedarf', 'is_correct' => false],
                    ['text' => 'Eine Anforderung, die bei begründeten Ausnahmen abgewogen werden kann', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was bedeutet das Modalverb "SOLLTE" in den Anforderungen des IT-Grundschutz-Kompendiums?',
                'explanation' => 'SOLLTE bedeutet, dass eine Anforderung normalerweise erfüllt werden muss, es aber Gründe geben kann, dies doch nicht zu tun. Die Abweichung muss sorgfältig abgewogen und stichhaltig begründet werden. Dies unterscheidet sich von MUSS, wo keine Risikoübernahme möglich ist.',
                'quote' => 'SOLLTE: Dieser Ausdruck bedeutet, dass eine Anforderung normalerweise erfüllt werden muss, es aber Gründe geben kann, dies doch nicht zu tun. Dies muss aber sorgfältig abgewogen und stichhaltig begründet werden.',
                'source' => 'IT-Grundschutz-Kompendium, IT-Grundschutz – Basis für Informationssicherheit, S. 6',
                'answers' => [
                    ['text' => 'Eine Anforderung, die normalerweise erfüllt werden muss, bei der aber begründete Abweichungen möglich sind', 'is_correct' => true],
                    ['text' => 'Eine uneingeschränkte Anforderung ohne Möglichkeit der Risikoübernahme', 'is_correct' => false],
                    ['text' => 'Eine rein informative Empfehlung ohne Verbindlichkeit', 'is_correct' => false],
                    ['text' => 'Eine Anforderung, die nur bei erhöhtem Schutzbedarf relevant ist', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'In welche drei Kategorien sind die Anforderungen der IT-Grundschutz-Bausteine gegliedert?',
                'explanation' => 'Die Anforderungen sind in Basis-Anforderungen, Standard-Anforderungen und Anforderungen bei erhöhtem Schutzbedarf gegliedert. Basis-Anforderungen sind vorrangig umzusetzen, Standard-Anforderungen decken den normalen Schutzbedarf, und Anforderungen bei erhöhtem Schutzbedarf geben Vorschläge für besonders schutzbedürftige Bereiche.',
                'quote' => 'Diese sind in drei Kategorien gegliedert: Basis- und Standard-Anforderungen sowie Anforderungen bei erhöhtem Schutzbedarf. Basis-Anforderungen sind vorrangig umzusetzen, da sie mit geringem Aufwand den größtmöglichen Nutzen erzielen.',
                'source' => 'IT-Grundschutz-Kompendium, IT-Grundschutz – Basis für Informationssicherheit, S. 5',
                'answers' => [
                    ['text' => 'Basis-Anforderungen', 'is_correct' => true],
                    ['text' => 'Standard-Anforderungen', 'is_correct' => true],
                    ['text' => 'Anforderungen bei erhöhtem Schutzbedarf', 'is_correct' => true],
                    ['text' => 'Mindestanforderungen', 'is_correct' => false],
                    ['text' => 'Optionale Anforderungen', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Wie viele elementare Gefährdungen umfasst das IT-Grundschutz-Kompendium?',
                'explanation' => 'Das BSI hat aus vielen spezifischen Einzelgefährdungen generelle Aspekte herausgearbeitet und in 47 sogenannte elementare Gefährdungen überführt. Diese bilden die Grundlage für die Kreuzreferenztabellen und die Risikoanalyse.',
                'quote' => 'Das BSI hat aus vielen spezifischen Einzelgefährdungen generelle Aspekte herausgearbeitet und in 47 sogenannte elementare Gefährdungen überführt.',
                'source' => 'IT-Grundschutz-Kompendium, IT-Grundschutz – Basis für Informationssicherheit, S. 4',
                'answers' => [
                    ['text' => '47', 'is_correct' => true],
                    ['text' => '42', 'is_correct' => false],
                    ['text' => '52', 'is_correct' => false],
                    ['text' => '111', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Wie viele IT-Grundschutz-Bausteine enthält die Edition 2023 des IT-Grundschutz-Kompendiums insgesamt?',
                'explanation' => 'Die Edition 2023 enthält insgesamt 111 IT-Grundschutz-Bausteine. Darunter sind zehn neue Bausteine sowie 101 Bausteine aus der Edition 2022. Drei Bausteine der Edition 2022 sind entfallen und 21 wurden überarbeitet.',
                'quote' => 'Die Edition 2023 des IT-Grundschutz-Kompendiums enthält insgesamt 111 IT-Grundschutz-Bausteine. Darunter sind zehn neue IT-Grundschutz-Bausteine sowie 101 Bausteine aus der Edition 2022.',
                'source' => 'IT-Grundschutz-Kompendium, Neues im IT-Grundschutz-Kompendium, S. 1',
                'answers' => [
                    ['text' => '111', 'is_correct' => true],
                    ['text' => '101', 'is_correct' => false],
                    ['text' => '121', 'is_correct' => false],
                    ['text' => '97', 'is_correct' => false],
                ],
            ],
            // === Schichtenmodell und Modellierung ===
            [
                'text' => 'In welche zwei Hauptgruppen sind die Bausteine des IT-Grundschutz-Kompendiums aufgeteilt?',
                'explanation' => 'Die Bausteine sind in Prozess-Bausteine und System-Bausteine aufgeteilt. Prozess-Bausteine gelten in der Regel für sämtliche oder große Teile des Informationsverbunds, System-Bausteine lassen sich auf einzelne Objekte oder Gruppen von Objekten anwenden.',
                'quote' => 'Um diese Auswahl zu erleichtern, sind die Bausteine im IT-Grundschutz-Kompendium zunächst in Prozess- und System-Bausteine aufgeteilt und diese jeweils in einzelne Schichten untergliedert.',
                'source' => 'IT-Grundschutz-Kompendium, Schichtenmodell und Modellierung, S. 1',
                'answers' => [
                    ['text' => 'Prozess-Bausteine und System-Bausteine', 'is_correct' => true],
                    ['text' => 'Technische Bausteine und Organisatorische Bausteine', 'is_correct' => false],
                    ['text' => 'Basis-Bausteine und Erweiterte Bausteine', 'is_correct' => false],
                    ['text' => 'Infrastruktur-Bausteine und Anwendungs-Bausteine', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Schichten gehören zu den Prozess-Bausteinen im Schichtenmodell des IT-Grundschutz-Kompendiums?',
                'explanation' => 'Die Prozess-Bausteine umfassen die Schichten ISMS (Sicherheitsmanagement), ORP (Organisation und Personal), CON (Konzepte und Vorgehensweisen), OPS (Betrieb) und DER (Detektion und Reaktion). Diese gelten in der Regel für den gesamten Informationsverbund.',
                'quote' => 'Die Schicht ISMS enthält als Grundlage für alle weiteren Aktivitäten im Sicherheitsprozess den Baustein Sicherheitsmanagement. Die Schicht ORP befasst sich mit organisatorischen und personellen Sicherheitsaspekten. [...] Die Schicht CON enthält Bausteine, die sich mit Konzepten und Vorgehensweisen befassen. [...] Die Schicht OPS umfasst alle Sicherheitsaspekte betrieblicher Art. [...] In der Schicht DER finden sich alle Bausteine, die für die Überprüfung der umgesetzten Sicherheitsmaßnahmen [...] relevant sind.',
                'source' => 'IT-Grundschutz-Kompendium, Schichtenmodell und Modellierung, S. 1-2',
                'answers' => [
                    ['text' => 'ISMS (Sicherheitsmanagement)', 'is_correct' => true],
                    ['text' => 'ORP (Organisation und Personal)', 'is_correct' => true],
                    ['text' => 'CON (Konzepte und Vorgehensweisen)', 'is_correct' => true],
                    ['text' => 'OPS (Betrieb)', 'is_correct' => true],
                    ['text' => 'DER (Detektion und Reaktion)', 'is_correct' => true],
                    ['text' => 'APP (Anwendungen)', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Schichten gehören zu den System-Bausteinen im Schichtenmodell?',
                'explanation' => 'Die System-Bausteine umfassen APP (Anwendungen), SYS (IT-Systeme), IND (Industrielle IT), NET (Netze und Kommunikation) und INF (Infrastruktur). Diese werden in der Regel auf einzelne Zielobjekte oder Gruppen von Zielobjekten angewendet.',
                'quote' => 'Die Schicht APP beschäftigt sich mit der Absicherung von Anwendungen und Diensten [...] Die Schicht SYS betrifft die einzelnen IT-Systeme des Informationsverbunds [...] Die Schicht IND befasst sich mit Sicherheitsaspekten industrieller IT. [...] Die Schicht NET betrachtet die Vernetzungsaspekte [...] Die Schicht INF befasst sich mit den baulich-technischen Gegebenheiten',
                'source' => 'IT-Grundschutz-Kompendium, Schichtenmodell und Modellierung, S. 2',
                'answers' => [
                    ['text' => 'APP (Anwendungen)', 'is_correct' => true],
                    ['text' => 'SYS (IT-Systeme)', 'is_correct' => true],
                    ['text' => 'IND (Industrielle IT)', 'is_correct' => true],
                    ['text' => 'NET (Netze und Kommunikation)', 'is_correct' => true],
                    ['text' => 'INF (Infrastruktur)', 'is_correct' => true],
                    ['text' => 'DER (Detektion und Reaktion)', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was bedeuten die Reihenfolge-Kennzeichnungen R1, R2 und R3 bei den Bausteinen?',
                'explanation' => 'R1-Bausteine sollten vorrangig umgesetzt werden, da sie die Grundlage für einen effektiven Sicherheitsprozess bilden. R2-Bausteine sollten als nächstes umgesetzt werden, da sie für nachhaltige Sicherheit erforderlich sind. R3-Bausteine werden zur Erreichung des angestrebten Sicherheitsniveaus ebenfalls benötigt, sollten aber erst nach den anderen betrachtet werden.',
                'quote' => 'R1: Diese Bausteine sollten vorrangig umgesetzt werden, da sie die Grundlage für einen effektiven Sicherheitsprozess bilden. R2: Diese Bausteine sollten als nächstes umgesetzt werden, da sie in wesentlichen Teilen des Informationsverbundes für nachhaltige Sicherheit erforderlich sind. R3: Diese Bausteine werden zur Erreichung des angestrebten Sicherheitsniveaus ebenfalls benötigt und müssen umgesetzt werden.',
                'source' => 'IT-Grundschutz-Kompendium, Schichtenmodell und Modellierung, S. 6',
                'answers' => [
                    ['text' => 'R1 = vorrangig umsetzen (Grundlage), R2 = als nächstes umsetzen (nachhaltige Sicherheit), R3 = ebenfalls benötigt (nach R1/R2)', 'is_correct' => true],
                    ['text' => 'R1 = niedrigstes Risiko, R2 = mittleres Risiko, R3 = höchstes Risiko', 'is_correct' => false],
                    ['text' => 'R1 = Basis-Absicherung, R2 = Standard-Absicherung, R3 = Kern-Absicherung', 'is_correct' => false],
                    ['text' => 'R1 = Pflicht, R2 = optional, R3 = nur bei erhöhtem Schutzbedarf', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welches IT-Grundschutz-Modell wird verwendet, wenn der Informationsverbund bereits realisiert und im Einsatz ist?',
                'explanation' => 'Bei einem bereits realisierten Informationsverbund dient das Modell als Prüfplan, um einen Soll-Ist-Vergleich durchzuführen. Bei einem geplanten Informationsverbund hingegen stellt es ein Entwicklungskonzept dar, das beschreibt, welche Sicherheitsanforderungen erfüllt werden müssen.',
                'quote' => 'Das IT-Grundschutz-Modell eines bereits realisierten Informationsverbunds identifiziert über die verwendeten Bausteine die relevanten Sicherheitsanforderungen. Es kann in Form eines Prüfplans benutzt werden, um einen Soll-Ist-Vergleich durchzuführen.',
                'source' => 'IT-Grundschutz-Kompendium, Schichtenmodell und Modellierung, S. 1',
                'answers' => [
                    ['text' => 'Ein Prüfplan für den Soll-Ist-Vergleich', 'is_correct' => true],
                    ['text' => 'Ein Entwicklungskonzept', 'is_correct' => false],
                    ['text' => 'Ein Notfallhandbuch', 'is_correct' => false],
                    ['text' => 'Ein Migrationsplan', 'is_correct' => false],
                ],
            ],
            // === Glossar und Schlüsselbegriffe ===
            [
                'text' => 'Welche drei Grundwerte der Informationssicherheit betrachtet der IT-Grundschutz?',
                'explanation' => 'Die drei Grundwerte sind Vertraulichkeit, Verfügbarkeit und Integrität. Darüber hinaus können weitere Grundwerte wie Authentizität, Verbindlichkeit, Zuverlässigkeit und Nichtabstreitbarkeit betrachtet werden, diese sind aber nicht die drei Kerngrundwerte.',
                'quote' => 'Der IT-Grundschutz betrachtet die drei Grundwerte der Informationssicherheit: Vertraulichkeit, Verfügbarkeit und Integrität.',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 3',
                'answers' => [
                    ['text' => 'Vertraulichkeit, Verfügbarkeit und Integrität', 'is_correct' => true],
                    ['text' => 'Vertraulichkeit, Authentizität und Verfügbarkeit', 'is_correct' => false],
                    ['text' => 'Integrität, Verbindlichkeit und Vertraulichkeit', 'is_correct' => false],
                    ['text' => 'Verfügbarkeit, Nichtabstreitbarkeit und Integrität', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was ist der Unterschied zwischen einer Bedrohung und einer Gefährdung im Kontext des IT-Grundschutzes?',
                'explanation' => 'Eine Gefährdung ist eine Bedrohung, die konkret über eine Schwachstelle auf ein Objekt einwirkt. Eine Bedrohung allein ist noch kein Problem — erst wenn sie auf eine Schwachstelle trifft, entsteht eine Gefährdung. Beispiel: Schadprogramme im Internet sind eine Bedrohung; sie werden erst zur Gefährdung, wenn ein System anfällig ist.',
                'quote' => 'Eine Gefährdung ist eine Bedrohung, die konkret über eine Schwachstelle auf ein Objekt einwirkt. Eine Bedrohung wird somit erst durch eine vorhandene Schwachstelle zur Gefährdung für ein Objekt.',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 3',
                'answers' => [
                    ['text' => 'Eine Gefährdung ist eine Bedrohung, die konkret über eine Schwachstelle auf ein Objekt einwirkt', 'is_correct' => true],
                    ['text' => 'Bedrohung und Gefährdung sind Synonyme im IT-Grundschutz', 'is_correct' => false],
                    ['text' => 'Eine Bedrohung ist immer vorsätzlich, eine Gefährdung immer zufällig', 'is_correct' => false],
                    ['text' => 'Eine Gefährdung ist technisch, eine Bedrohung organisatorisch', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was unterscheidet die Begriffe Zugang, Zugriff und Zutritt im IT-Grundschutz?',
                'explanation' => 'Zutritt bezieht sich auf das physische Betreten von Räumen und Gebäuden. Zugang betrifft die Nutzung von IT-Systemen, System-Komponenten und Netzen. Zugriff regelt die Nutzung von Informationen oder Daten. Diese Differenzierung ist wichtig für das Berechtigungsmanagement.',
                'quote' => 'Zugang: Mit Zugang wird die Nutzung von IT-Systemen, System-Komponenten und Netzen bezeichnet. [...] Zugriff: Mit Zugriff wird die Nutzung von Informationen oder Daten bezeichnet. [...] Zutritt: Mit Zutritt wird das Betreten von abgegrenzten Bereichen wie z. B. Räumen oder geschützten Arealen in einem Gelände bezeichnet.',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 9',
                'answers' => [
                    ['text' => 'Zutritt = physisches Betreten von Räumen, Zugang = Nutzung von IT-Systemen, Zugriff = Nutzung von Informationen/Daten', 'is_correct' => true],
                    ['text' => 'Alle drei Begriffe sind synonym und bezeichnen die Nutzung von IT-Systemen', 'is_correct' => false],
                    ['text' => 'Zutritt = Nutzung von Informationen, Zugang = Betreten von Räumen, Zugriff = Nutzung von IT-Systemen', 'is_correct' => false],
                    ['text' => 'Zutritt und Zugang sind physisch, Zugriff ist nur digital', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was versteht man unter dem Maximumprinzip bei der Schutzbedarfsfeststellung?',
                'explanation' => 'Nach dem Maximumprinzip bestimmt der Schaden bzw. die Summe der Schäden mit den schwerwiegendsten Auswirkungen den Schutzbedarf eines Geschäftsprozesses, einer Anwendung oder eines IT-Systems. Es wird also das Maximum der möglichen Schäden als Maßstab genommen.',
                'quote' => 'Nach dem Maximumprinzip bestimmt der Schaden bzw. die Summe der Schäden mit den schwerwiegendsten Auswirkungen den Schutzbedarf eines Geschäftsprozesses, einer Anwendung bzw. eines IT-Systems.',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 5',
                'answers' => [
                    ['text' => 'Der schwerwiegendste Schaden bestimmt den Schutzbedarf', 'is_correct' => true],
                    ['text' => 'Der Durchschnitt aller Schäden bestimmt den Schutzbedarf', 'is_correct' => false],
                    ['text' => 'Die Summe aller Einzelrisiken ergibt den Schutzbedarf', 'is_correct' => false],
                    ['text' => 'Der häufigste Schadentyp bestimmt den Schutzbedarf', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was versteht man unter dem Kumulationseffekt im IT-Grundschutz?',
                'explanation' => 'Der Kumulationseffekt beschreibt, dass sich der Schutzbedarf eines IT-Systems erhöhen kann, wenn durch Kumulation mehrerer kleinerer Schäden ein insgesamt höherer Gesamtschaden entsteht. Dies kann z.B. auftreten, wenn viele sensitive Anwendungen auf einem System laufen.',
                'quote' => 'Der Kumulationseffekt beschreibt, dass sich der Schutzbedarf eines IT-Systems erhöhen kann, wenn durch Kumulation mehrerer (z. B. kleinerer) Schäden auf einem IT-System ein insgesamt höherer Gesamtschaden entstehen kann.',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 5',
                'answers' => [
                    ['text' => 'Erhöhung des Schutzbedarfs durch Kumulation mehrerer kleinerer Schäden zu einem höheren Gesamtschaden', 'is_correct' => true],
                    ['text' => 'Verringerung des Schutzbedarfs durch Verteilung auf mehrere Systeme', 'is_correct' => false],
                    ['text' => 'Summierung aller Sicherheitsanforderungen über alle Bausteine', 'is_correct' => false],
                    ['text' => 'Übertragung des Schutzbedarfs von einem System auf ein anderes', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was versteht man unter dem Verteilungseffekt im IT-Grundschutz?',
                'explanation' => 'Der Verteilungseffekt kann den Schutzbedarf relativieren: Wenn eine Anwendung zwar einen hohen Schutzbedarf besitzt, aber nur unwesentliche Teilbereiche auf ein betrachtetes IT-System überträgt, kann der Schutzbedarf dieses Systems niedriger als der der Gesamtanwendung sein.',
                'quote' => 'Der Verteilungseffekt kann sich auf den Schutzbedarf relativierend auswirken, wenn zwar eine Anwendung einen hohen Schutzbedarf besitzt, ihn aber deshalb nicht auf ein betrachtetes IT-System überträgt, weil auf diesem IT-System nur unwesentliche Teilbereiche der Anwendung ausgeführt werden.',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 8',
                'answers' => [
                    ['text' => 'Relativierung des Schutzbedarfs, wenn nur unwesentliche Teilbereiche einer Anwendung auf einem System laufen', 'is_correct' => true],
                    ['text' => 'Erhöhung des Schutzbedarfs durch Verteilung auf mehrere Systeme', 'is_correct' => false],
                    ['text' => 'Gleichmäßige Verteilung aller Sicherheitsmaßnahmen im Informationsverbund', 'is_correct' => false],
                    ['text' => 'Kumulation von Risiken durch parallele Systeme', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussagen zur Informationssicherheitsrevision (IS-Revision) sind korrekt?',
                'explanation' => 'Die IS-Revision ist ein Bestandteil jedes erfolgreichen Informationssicherheitsmanagements. Nur durch regelmäßige Überprüfung können Aussagen über Umsetzung, Aktualität, Vollständigkeit und Angemessenheit der Informationssicherheit getroffen werden.',
                'quote' => 'Informationssicherheitsrevision (IS-Revision) ist ein Bestandteil eines jeden erfolgreichen Informationssicherheitsmanagements. Nur durch die regelmäßige Überprüfung der etablierten Sicherheitsmaßnahmen und des Informationssicherheits-Prozesses können Aussagen über deren wirksame Umsetzung, Aktualität, Vollständigkeit und Angemessenheit und damit über den aktuellen Zustand der Informationssicherheit getroffen werden.',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 4',
                'answers' => [
                    ['text' => 'Sie ist Bestandteil jedes erfolgreichen ISMS', 'is_correct' => true],
                    ['text' => 'Sie prüft Umsetzung, Aktualität, Vollständigkeit und Angemessenheit', 'is_correct' => true],
                    ['text' => 'Sie muss regelmäßig durchgeführt werden', 'is_correct' => true],
                    ['text' => 'Sie ist nur bei der erstmaligen Zertifizierung erforderlich', 'is_correct' => false],
                    ['text' => 'Sie ersetzt die Risikoanalyse nach BSI-Standard 200-3', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was ist laut IT-Grundschutz ein Informationsverbund?',
                'explanation' => 'Ein Informationsverbund ist die Gesamtheit von infrastrukturellen, organisatorischen, personellen und technischen Objekten, die der Aufgabenerfüllung in einem bestimmten Anwendungsbereich der Informationsverarbeitung dienen. Er kann die gesamte Institution oder einzelne Bereiche umfassen.',
                'quote' => 'Unter einem Informationsverbund ist die Gesamtheit von infrastrukturellen, organisatorischen, personellen und technischen Objekten zu verstehen, die der Aufgabenerfüllung in einem bestimmten Anwendungsbereich der Informationsverarbeitung dienen.',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 4',
                'answers' => [
                    ['text' => 'Die Gesamtheit von infrastrukturellen, organisatorischen, personellen und technischen Objekten für einen Anwendungsbereich', 'is_correct' => true],
                    ['text' => 'Ausschließlich die vernetzten IT-Systeme einer Institution', 'is_correct' => false],
                    ['text' => 'Die Gesamtheit aller Server und Clients im Rechenzentrum', 'is_correct' => false],
                    ['text' => 'Das Netzwerk zwischen verschiedenen Standorten einer Behörde', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was sind Kronjuwelen im Kontext des IT-Grundschutzes?',
                'explanation' => 'Als Kronjuwelen werden Assets bezeichnet, deren Diebstahl, Zerstörung oder Kompromittierung einen existenzbedrohenden Schaden für die Institution bedeuten würde. Diese erfordern besonderen Schutz.',
                'quote' => 'Als Kronjuwelen werden solche Assets bezeichnet, deren Diebstahl, Zerstörung oder Kompromittierung einen existenzbedrohenden Schaden für die Institution bedeuten würde.',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 5',
                'answers' => [
                    ['text' => 'Assets, deren Kompromittierung einen existenzbedrohenden Schaden verursachen würde', 'is_correct' => true],
                    ['text' => 'Die physischen Server im Hochsicherheitsbereich', 'is_correct' => false],
                    ['text' => 'Zertifizierungen und Auditergebnisse einer Institution', 'is_correct' => false],
                    ['text' => 'Die wichtigsten Sicherheitsmaßnahmen einer Institution', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussage zur Starken Authentisierung ist korrekt?',
                'explanation' => 'Starke Authentisierung bezeichnet die Kombination von zwei oder mehr Authentisierungstechniken, wie Passwort plus Transaktionsnummern (Einmalpasswörter) oder plus Chipkarte. Dies wird auch als Zwei- oder Mehr-Faktor-Authentisierung bezeichnet.',
                'quote' => 'Starke Authentisierung bezeichnet die Kombination von zwei oder mehr Authentisierungstechniken, wie Passwort plus Transaktionsnummern (Einmalpasswörter) oder plus Chipkarte. Daher wird dies auch häufig als Zwei- oder Mehr-Faktor-Authentisierung bezeichnet.',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 8',
                'answers' => [
                    ['text' => 'Sie erfordert die Kombination von zwei oder mehr Authentisierungstechniken', 'is_correct' => true],
                    ['text' => 'Sie erfordert lediglich ein besonders langes und komplexes Passwort', 'is_correct' => false],
                    ['text' => 'Sie ist ausschließlich über biometrische Verfahren möglich', 'is_correct' => false],
                    ['text' => 'Sie bezeichnet die einmalige Authentisierung am Anfang einer Session', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Schutzbedarfskategorien kennt der IT-Grundschutz?',
                'explanation' => 'Der IT-Grundschutz unterscheidet drei Schutzbedarfskategorien: "normal", "hoch" und "sehr hoch". Bewährt hat sich diese Einteilung, um den Schutzbedarf realistisch einzuschätzen und angemessene Sicherheitsmaßnahmen auszuwählen.',
                'quote' => 'Bewährt hat sich eine Einteilung in die drei Schutzbedarfskategorien „normal", „hoch" und „sehr hoch".',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 7',
                'answers' => [
                    ['text' => 'Normal, hoch und sehr hoch', 'is_correct' => true],
                    ['text' => 'Niedrig, mittel und hoch', 'is_correct' => false],
                    ['text' => 'Basis, Standard und Erweitert', 'is_correct' => false],
                    ['text' => 'Gering, normal, hoch und sehr hoch', 'is_correct' => false],
                ],
            ],
            // === Rollen ===
            [
                'text' => 'Wer ist laut IT-Grundschutz der Informationssicherheitsbeauftragte (ISB)?',
                'explanation' => 'Der ISB ist eine von der Institutionsleitung ernannte Person, die im Auftrag der Leitungsebene die Aufgabe Informationssicherheit koordiniert und innerhalb der Behörde bzw. des Unternehmens vorantreibt. Andere Bezeichnungen sind CISO oder ISM.',
                'quote' => 'Informationssicherheitsbeauftragte sind von der Institutionsleitung ernannte Personen, die im Auftrag der Leitungsebene die Aufgabe Informationssicherheit koordinieren und innerhalb der Behörde bzw. des Unternehmens vorantreiben.',
                'source' => 'IT-Grundschutz-Kompendium, Rollen, S. 2',
                'answers' => [
                    ['text' => 'Eine von der Institutionsleitung ernannte Person, die Informationssicherheit koordiniert und vorantreibt', 'is_correct' => true],
                    ['text' => 'Der technische Leiter der IT-Abteilung', 'is_correct' => false],
                    ['text' => 'Ein externer Auditor, der die Sicherheitsmaßnahmen prüft', 'is_correct' => false],
                    ['text' => 'Der Datenschutzbeauftragte in Personalunion', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was versteht man unter der Rolle "IT-Betrieb" im IT-Grundschutz?',
                'explanation' => 'Als IT-Betrieb wird die Organisationseinheit bezeichnet, die die interne IT einrichtet, betreibt, überwacht und wartet. Die Rolle schließt die zuständige Leitung der Organisationseinheit mit ein.',
                'quote' => 'Als IT-Betrieb wird die Organisationseinheit bezeichnet, die die interne IT einrichtet, betreibt, überwacht und wartet. Die Rolle IT-Betrieb schließt die zuständige Leitung der Organisationseinheit mit ein.',
                'source' => 'IT-Grundschutz-Kompendium, Rollen, S. 2',
                'answers' => [
                    ['text' => 'Die Organisationseinheit, die die interne IT einrichtet, betreibt, überwacht und wartet', 'is_correct' => true],
                    ['text' => 'Der tägliche Betrieb der Serverinfrastruktur im Rechenzentrum', 'is_correct' => false],
                    ['text' => 'Ein externer Dienstleister für den IT-Support', 'is_correct' => false],
                    ['text' => 'Die Abteilung für Software-Entwicklung', 'is_correct' => false],
                ],
            ],
            // === Elementare Gefährdungen ===
            [
                'text' => 'Was versteht man im IT-Grundschutz unter Social Engineering?',
                'explanation' => 'Social Engineering ist eine Methode, um unberechtigten Zugang zu Informationen oder IT-Systemen durch soziale Handlungen zu erlangen. Dabei werden menschliche Eigenschaften wie Hilfsbereitschaft, Vertrauen, Angst oder Respekt vor Autorität ausgenutzt. Phishing ist eine häufige Form des Social Engineering.',
                'quote' => 'Social Engineering ist eine Methode, um unberechtigten Zugang zu Informationen oder IT-Systemen durch soziale Handlungen zu erlangen. Beim Social Engineering werden menschliche Eigenschaften wie z. B. Hilfsbereitschaft, Vertrauen, Angst oder Respekt vor Autorität ausgenutzt.',
                'source' => 'IT-Grundschutz-Kompendium, Elementare Gefährdungen, G 0.42, S. 42',
                'answers' => [
                    ['text' => 'Eine Methode zum Erlangen unberechtigten Zugangs durch Ausnutzung menschlicher Eigenschaften', 'is_correct' => true],
                    ['text' => 'Ein technischer Angriff auf soziale Netzwerke', 'is_correct' => false],
                    ['text' => 'Die Manipulation von Social-Media-Profilen zur Datengewinnung', 'is_correct' => false],
                    ['text' => 'Eine Softwareentwicklungsmethode mit sozialer Komponente', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche menschlichen Eigenschaften werden beim Social Engineering typischerweise ausgenutzt?',
                'explanation' => 'Laut IT-Grundschutz-Kompendium werden beim Social Engineering typischerweise Hilfsbereitschaft, Vertrauen, Angst und Respekt vor Autorität ausgenutzt. Ein typischer Fall ist z.B. ein fingierter Telefonanruf, bei dem sich Angreifende als Vorgesetzte oder IT-Mitarbeitende ausgeben.',
                'quote' => 'Beim Social Engineering werden menschliche Eigenschaften wie z. B. Hilfsbereitschaft, Vertrauen, Angst oder Respekt vor Autorität ausgenutzt. Dadurch können Mitarbeitende so manipuliert werden, dass sie unzulässig handeln.',
                'source' => 'IT-Grundschutz-Kompendium, Elementare Gefährdungen, G 0.42, S. 42',
                'answers' => [
                    ['text' => 'Hilfsbereitschaft', 'is_correct' => true],
                    ['text' => 'Vertrauen', 'is_correct' => true],
                    ['text' => 'Angst', 'is_correct' => true],
                    ['text' => 'Respekt vor Autorität', 'is_correct' => true],
                    ['text' => 'Technisches Fachwissen', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was ist ein Schadprogramm laut IT-Grundschutz-Kompendium?',
                'explanation' => 'Ein Schadprogramm ist Software, die mit dem Ziel entwickelt wurde, unerwünschte und meistens schädliche Funktionen auszuführen. Zu den typischen Arten gehören Viren, Würmer und Trojanische Pferde. Schadprogramme werden meist heimlich und ohne Wissen und Einwilligung der Benutzenden aktiv.',
                'quote' => 'Ein Schadprogramm ist eine Software, die mit dem Ziel entwickelt wurde, unerwünschte und meistens schädliche Funktionen auszuführen. Zu den typischen Arten von Schadprogrammen gehören unter anderem Viren, Würmer und Trojanische Pferde. Schadprogramme werden meist heimlich, ohne Wissen und Einwilligung der Benutzenden aktiv.',
                'source' => 'IT-Grundschutz-Kompendium, Elementare Gefährdungen, G 0.39, S. 39',
                'answers' => [
                    ['text' => 'Software, die mit dem Ziel entwickelt wurde, unerwünschte und meistens schädliche Funktionen auszuführen', 'is_correct' => true],
                    ['text' => 'Jede Software, die Sicherheitslücken enthält', 'is_correct' => false],
                    ['text' => 'Software, die ohne Lizenz betrieben wird', 'is_correct' => false],
                    ['text' => 'Jede unbekannte Software auf einem IT-System', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche zwei Spezialfälle des Einspielens von Nachrichten werden im IT-Grundschutz-Kompendium genannt?',
                'explanation' => 'Das Kompendium nennt die Replay-Attacke (Wiedereinspielen aufgezeichneter Nachrichten) und die Man-in-the-Middle-Attacke als die zwei wichtigen Spezialfälle. Bei der Replay-Attacke werden gültige Nachrichten erneut eingespielt, bei der MitM-Attacke schaltet sich der Angreifende in die Kommunikation ein.',
                'quote' => 'Es gibt zwei in der Praxis wichtige Spezialfälle des Einspielens von Nachrichten: Bei einer „Replay-Attacke" (Wiedereinspielen von Nachrichten) werden gültige Nachrichten aufgezeichnet und zu einem späteren Zeitpunkt nahezu unverändert wieder eingespielt. [...] Bei einer „Man-in-the-Middle-Attacke" nehmen Angreifende unbemerkt eine Vermittlungsposition in der Kommunikation zwischen verschiedenen Teilnehmenden ein.',
                'source' => 'IT-Grundschutz-Kompendium, Elementare Gefährdungen, G 0.43, S. 43',
                'answers' => [
                    ['text' => 'Replay-Attacke und Man-in-the-Middle-Attacke', 'is_correct' => true],
                    ['text' => 'Brute-Force-Attacke und Dictionary-Attacke', 'is_correct' => false],
                    ['text' => 'Phishing-Attacke und Pharming-Attacke', 'is_correct' => false],
                    ['text' => 'SQL-Injection und Cross-Site-Scripting', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was beschreibt die elementare Gefährdung G 0.40 "Verhinderung von Diensten (Denial of Service)"?',
                'explanation' => 'DoS-Angriffe zielen darauf ab, die vorgesehene Nutzung bestimmter Dienstleistungen, Funktionen oder Geräte zu verhindern. Bei IT-basierten Angriffen werden Ressourcen wie Prozesse, CPU-Zeit, Arbeitsspeicher, Plattenplatz und Übertragungskapazität künstlich verknappt.',
                'quote' => 'Es gibt eine Vielzahl verschiedener Angriffsformen, die darauf abzielen, die vorgesehene Nutzung bestimmter Dienstleistungen, Funktionen oder Geräte zu verhindern. Der Oberbegriff für solche Angriffe ist „Verhinderung von Diensten" (englisch: „Denial of Service").',
                'source' => 'IT-Grundschutz-Kompendium, Elementare Gefährdungen, G 0.40, S. 40',
                'answers' => [
                    ['text' => 'Angriffe, die darauf abzielen, die vorgesehene Nutzung von Diensten, Funktionen oder Geräten zu verhindern', 'is_correct' => true],
                    ['text' => 'Angriffe, die ausschließlich Webserver durch Überlastung lahmlegen', 'is_correct' => false],
                    ['text' => 'Physische Sabotage von Netzwerkkomponenten', 'is_correct' => false],
                    ['text' => 'Angriffe, die ausschließlich über das Internet erfolgen', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Auswirkungen können schädliche Seiteneffekte IT-gestützter Angriffe (G 0.47) haben?',
                'explanation' => 'IT-gestützte Angriffe können Auswirkungen haben, die von den Angreifenden nicht beabsichtigt sind, nicht die unmittelbar angegriffenen Zielobjekte betreffen, oder unbeteiligte Dritte schädigen. Beispielsweise können Bots auf IT-Systemen für DDoS-Angriffe gegen Dritte genutzt werden.',
                'quote' => 'IT-gestützte Angriffe können Auswirkungen haben, die von den Angreifenden nicht beabsichtigt sind oder nicht die unmittelbar angegriffenen Zielobjekte betreffen oder unbeteiligte Dritte schädigen.',
                'source' => 'IT-Grundschutz-Kompendium, Elementare Gefährdungen, G 0.47, S. 47',
                'answers' => [
                    ['text' => 'Auswirkungen, die von Angreifenden nicht beabsichtigt sind', 'is_correct' => true],
                    ['text' => 'Schäden an nicht unmittelbar angegriffenen Zielobjekten', 'is_correct' => true],
                    ['text' => 'Schädigung unbeteiligter Dritter', 'is_correct' => true],
                    ['text' => 'Ausschließlich finanzielle Schäden beim Angreifenden selbst', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Gefährdungen durch Abhören (G 0.15) werden als besonders kritisch eingestuft?',
                'explanation' => 'Besonders kritisch ist die ungeschützte Übertragung von Authentisierungsdaten bei Klartextprotokollen wie HTTP, FTP oder Telnet, da die klare Strukturierung der Daten eine automatische Analyse ermöglicht. Unverschlüsselte E-Mails werden mit Postkarten verglichen.',
                'quote' => 'Besonders kritisch ist die ungeschützte Übertragung von Authentisierungsdaten bei Klartextprotokollen wie HTTP, FTP oder Telnet, da diese durch die klare Strukturierung der Daten leicht automatisch zu analysieren sind.',
                'source' => 'IT-Grundschutz-Kompendium, Elementare Gefährdungen, G 0.15, S. 15',
                'answers' => [
                    ['text' => 'Ungeschützte Übertragung von Authentisierungsdaten bei Klartextprotokollen wie HTTP, FTP oder Telnet', 'is_correct' => true],
                    ['text' => 'Abhören von verschlüsselten VPN-Verbindungen', 'is_correct' => false],
                    ['text' => 'Mithören von Telefonaten über ISDN-Leitungen', 'is_correct' => false],
                    ['text' => 'Auslesen von Daten aus Glasfaserkabeln', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was wird laut IT-Grundschutz-Kompendium unter Nichtabstreitbarkeit (Non-Repudiation) verstanden?',
                'explanation' => 'Bei der Nichtabstreitbarkeit liegt der Schwerpunkt auf der Nachweisbarkeit gegenüber Dritten. Es wird zwischen Nichtabstreitbarkeit der Herkunft (Absender kann Versand nicht bestreiten) und Nichtabstreitbarkeit des Erhalts (Empfänger kann Empfang nicht bestreiten) unterschieden.',
                'quote' => 'Hierbei liegt der Schwerpunkt auf der Nachweisbarkeit gegenüber Dritten. Ziel ist es zu gewährleisten, dass der Versand und Empfang von Daten und Informationen nicht in Abrede gestellt werden kann. Es wird unterschieden zwischen Nichtabstreitbarkeit der Herkunft [...] Nichtabstreitbarkeit des Erhalts',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 5',
                'answers' => [
                    ['text' => 'Nachweisbarkeit gegenüber Dritten, dass Versand und Empfang von Daten nicht bestritten werden können', 'is_correct' => true],
                    ['text' => 'Verschlüsselung aller Kommunikation zwischen zwei Parteien', 'is_correct' => false],
                    ['text' => 'Protokollierung aller Zugriffe auf IT-Systeme', 'is_correct' => false],
                    ['text' => 'Sicherstellung der Verfügbarkeit von IT-Diensten', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was wird unter Verbindlichkeit im IT-Grundschutz verstanden?',
                'explanation' => 'Unter Verbindlichkeit werden die Sicherheitsziele Authentizität und Nichtabstreitbarkeit zusammengefasst. Bei der Übertragung von Informationen bedeutet dies, dass die Informationsquelle ihre Identität bewiesen hat und der Empfang der Nachricht nicht in Abrede gestellt werden kann.',
                'quote' => 'Unter Verbindlichkeit werden die Sicherheitsziele Authentizität und Nichtabstreitbarkeit zusammengefasst. Bei der Übertragung von Informationen bedeutet dies, dass die Informationsquelle ihre Identität bewiesen hat und der Empfang der Nachricht nicht in Abrede gestellt werden kann.',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 8',
                'answers' => [
                    ['text' => 'Zusammenfassung der Sicherheitsziele Authentizität und Nichtabstreitbarkeit', 'is_correct' => true],
                    ['text' => 'Synonym für Integrität im Kontext von Datenübertragungen', 'is_correct' => false],
                    ['text' => 'Rechtliche Bindungswirkung von IT-Sicherheitsrichtlinien', 'is_correct' => false],
                    ['text' => 'Verfügbarkeitsgarantie in Service Level Agreements', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussagen zur kompromittierenden Abstrahlung (G 0.13) sind korrekt?',
                'explanation' => 'Elektrische Geräte strahlen elektromagnetische Wellen ab. Bei Geräten, die Informationen verarbeiten (Computer, Bildschirme, Drucker), kann diese Strahlung auch Informationen enthalten. Diese wird als bloßstellende oder kompromittierende Abstrahlung bezeichnet. Auch Schallwellen (z.B. bei Druckern/Tastaturen) können Informationen preisgeben.',
                'quote' => 'Elektrische Geräte strahlen elektromagnetische Wellen ab. Bei Geräten, die Informationen verarbeiten (z. B. Computer, Bildschirme, Netzkoppelelemente, Drucker), kann diese Strahlung auch die gerade verarbeiteten Informationen mit sich führen. Derartige informationstragende Abstrahlung wird bloßstellende oder kompromittierende Abstrahlung genannt.',
                'source' => 'IT-Grundschutz-Kompendium, Elementare Gefährdungen, G 0.13, S. 13',
                'answers' => [
                    ['text' => 'Informationsverarbeitende Geräte können Informationen über elektromagnetische Strahlung preisgeben', 'is_correct' => true],
                    ['text' => 'Auch Schallwellen (z.B. bei Druckern oder Tastaturen) können nützliche Informationen enthalten', 'is_correct' => true],
                    ['text' => 'Die EMVG-Grenzwerte reichen im Allgemeinen nicht aus, um kompromittierende Abstrahlung zu verhindern', 'is_correct' => true],
                    ['text' => 'Kompromittierende Abstrahlung betrifft nur CRT-Bildschirme, nicht moderne LCD-Displays', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Aussage zum Phishing ist laut IT-Grundschutz-Kompendium korrekt?',
                'explanation' => 'Wenn sich Angreifende unerlaubt Passwörter oder andere Authentisierungsmerkmale verschaffen, beispielsweise mit Hilfe von Social Engineering, wird dies häufig als „Phishing" (Kunstwort aus „Password" und „Fishing") bezeichnet.',
                'quote' => 'Wenn sich Angreifende unerlaubt Passwörter oder andere Authentisierungsmerkmale verschaffen, beispielsweise mit Hilfe von Social Engineering, wird dies häufig auch als „Phishing" (Kunstwort aus „Password" und „Fishing") bezeichnet.',
                'source' => 'IT-Grundschutz-Kompendium, Elementare Gefährdungen, G 0.42, S. 42',
                'answers' => [
                    ['text' => 'Phishing ist ein Kunstwort aus "Password" und "Fishing" und bezeichnet das unerlaubte Verschaffen von Authentisierungsmerkmalen', 'is_correct' => true],
                    ['text' => 'Phishing bezeichnet ausschließlich das Versenden gefälschter E-Mails', 'is_correct' => false],
                    ['text' => 'Phishing ist eine rein technische Angriffsmethode ohne Social-Engineering-Anteil', 'is_correct' => false],
                    ['text' => 'Der Begriff Phishing stammt aus dem Bereich der Netzwerk-Protokollanalyse', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Vorgehensweisen (Absicherungsstrategien) bietet der IT-Grundschutz an?',
                'explanation' => 'Der IT-Grundschutz bietet drei Absicherungsstrategien: Die Basis-Absicherung ermöglicht einen grundlegenden Einstieg. Die Standard-Absicherung entspricht im Wesentlichen der klassischen Vorgehensweise des BSI-Standards 100-2. Die Kern-Absicherung fokussiert zunächst auf die besonders gefährdeten Geschäftsprozesse und Assets.',
                'quote' => 'Im IT-Grundschutz-Kompendium werden standardisierte Sicherheitsanforderungen für typische Geschäftsprozesse, Anwendungen, IT-Systeme, Kommunikationsverbindungen, Gebäude und Räume in IT-Grundschutz-Bausteinen beschrieben. [...] Die Kombination aus den IT-Grundschutz-Vorgehensweisen Basis-, Kern- und Standard-Absicherung sowie dem IT-Grundschutz-Kompendium bieten für unterschiedliche Einsatzumgebungen Sicherheitsanforderungen',
                'source' => 'IT-Grundschutz-Kompendium, IT-Grundschutz – Basis für Informationssicherheit, S. 1',
                'answers' => [
                    ['text' => 'Basis-Absicherung', 'is_correct' => true],
                    ['text' => 'Standard-Absicherung', 'is_correct' => true],
                    ['text' => 'Kern-Absicherung', 'is_correct' => true],
                    ['text' => 'Erweiterte Absicherung', 'is_correct' => false],
                    ['text' => 'Minimale Absicherung', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was beschreibt die Kern-Absicherung im IT-Grundschutz?',
                'explanation' => 'Im Fokus der Kern-Absicherung stehen zunächst die besonders gefährdeten Geschäftsprozesse und Assets. Die Kern-Absicherung richtet sich an Institutionen, die ihre wichtigsten ("Kronjuwelen") Geschäftsprozesse und Assets priorisiert absichern möchten.',
                'quote' => 'Im Fokus der Kern-Absicherung stehen zunächst die besonders gefährdeten Geschäftsprozesse und Assets.',
                'source' => 'IT-Grundschutz-Kompendium, Glossar, S. 5',
                'answers' => [
                    ['text' => 'Priorisierte Absicherung der besonders gefährdeten Geschäftsprozesse und Assets', 'is_correct' => true],
                    ['text' => 'Vollständige Umsetzung aller Basis- und Standard-Anforderungen', 'is_correct' => false],
                    ['text' => 'Grundlegende Erst-Absicherung aller Geschäftsprozesse', 'is_correct' => false],
                    ['text' => 'Ausschließliche Absicherung der Netzwerk-Kerninfrastruktur', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was beschreibt die elementare Gefährdung G 0.19 "Offenlegung schützenswerter Informationen"?',
                'explanation' => 'Vertrauliche Daten und Informationen dürfen nur berechtigten Personen zugänglich sein. Die Offenlegung kann durch technisches Versagen, Unachtsamkeit oder vorsätzliche Handlungen geschehen und weitreichende Folgen haben, wie Verstoß gegen Gesetze, negative Innen- und Außenwirkung, finanzielle Auswirkungen oder Beeinträchtigung des informationellen Selbstbestimmungsrechts.',
                'quote' => 'Vertrauliche Daten und Informationen dürfen nur den zur Kenntnisnahme berechtigten Personen zugänglich sein. [...] Für vertrauliche Informationen (wie Passwörter, personenbezogene Daten, Firmen- oder Amtsgeheimnisse, Entwicklungsdaten) besteht die inhärente Gefahr, dass diese durch technisches Versagen, Unachtsamkeit oder auch durch vorsätzliche Handlungen offengelegt werden.',
                'source' => 'IT-Grundschutz-Kompendium, Elementare Gefährdungen, G 0.19, S. 19',
                'answers' => [
                    ['text' => 'Vertrauliche Informationen gelangen an nicht berechtigte Personen durch technisches Versagen, Unachtsamkeit oder Vorsatz', 'is_correct' => true],
                    ['text' => 'Ausschließlich absichtliche Weitergabe geheimer Dokumente an Dritte', 'is_correct' => false],
                    ['text' => 'Veröffentlichung von Informationen über Sicherheitslücken', 'is_correct' => false],
                    ['text' => 'Transparente Kommunikation von Sicherheitsvorfällen an die Öffentlichkeit', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was ist laut IT-Grundschutz-Kompendium KEIN typisches Beispiel für einen Versorgungsnetzausfall (G 0.10)?',
                'explanation' => 'In Gebäuden gibt es verschiedene Versorgungsnetze wie Strom, Telefon, Kühlung, Heizung/Lüftung, Wasser/Abwasser, Löschwasserspeisungen, Gas, Melde-/Steueranlagen und Sprechanlagen. Internet-Anbindungen sind davon separat unter G 0.9 (Kommunikationsnetze) erfasst.',
                'quote' => 'Es gibt in einem Gebäude eine Vielzahl von Netzen, die der grundlegenden Ver- und Entsorgung und somit als Basis für alle Geschäftsprozesse einer Institution einschließlich der IT dienen. Beispiele für solche Versorgungsnetze sind: Strom, Telefon, Kühlung, Heizung bzw. Lüftung, Wasser und Abwasser, Löschwasserspeisungen, Gas, Melde- und Steueranlagen [...] und Sprechanlagen.',
                'source' => 'IT-Grundschutz-Kompendium, Elementare Gefährdungen, G 0.10, S. 10',
                'answers' => [
                    ['text' => 'Internet-Breitbandanbindung', 'is_correct' => true],
                    ['text' => 'Strom', 'is_correct' => false],
                    ['text' => 'Kühlung', 'is_correct' => false],
                    ['text' => 'Löschwasserspeisungen', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Entwicklungen in der Informationstechnik werden im IT-Grundschutz-Kompendium als besonders erwähnenswert für die Informationssicherheit genannt?',
                'explanation' => 'Das Kompendium nennt den steigenden Vernetzungsgrad, IT-Verbreitung und Durchdringung, das Verschwinden der Netzgrenzen, kürzere Angriffszyklen, höhere Interaktivität von Anwendungen und die Verantwortung der Benutzenden als zentrale Entwicklungen.',
                'quote' => 'Steigender Vernetzungsgrad [...] IT-Verbreitung und Durchdringung [...] Verschwinden der Netzgrenzen [...] Kürzere Angriffszyklen [...] Höhere Interaktivität von Anwendungen [...] Verantwortung der Benutzenden',
                'source' => 'IT-Grundschutz-Kompendium, IT-Grundschutz – Basis für Informationssicherheit, S. 2',
                'answers' => [
                    ['text' => 'Steigender Vernetzungsgrad', 'is_correct' => true],
                    ['text' => 'Kürzere Angriffszyklen', 'is_correct' => true],
                    ['text' => 'Verschwinden der Netzgrenzen', 'is_correct' => true],
                    ['text' => 'Sinkende Kosten für Sicherheitssoftware', 'is_correct' => false],
                ],
            ],
        ];

        foreach ($questions as $questionData) {
            $question = Question::create([
                'module_id' => $module->id,
                'text' => $questionData['text'],
                'explanation' => $questionData['explanation'],
                'quote' => $questionData['quote'],
                'source' => $questionData['source'],
            ]);

            foreach ($questionData['answers'] as $answerData) {
                Answer::create([
                    'question_id' => $question->id,
                    'text' => $answerData['text'],
                    'is_correct' => $answerData['is_correct'],
                ]);
            }
        }
    }
}
