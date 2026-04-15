import LegalLayout from '@/layouts/legal-layout';

export default function AGB() {
    return (
        <LegalLayout title="Allgemeine Geschäftsbedingungen (AGB)" headTitle="AGB · Prüfungstrainer">
            <section>
                            <h2 className="text-lg font-semibold">§ 1 Geltungsbereich</h2>
                            <p className="mt-2 text-muted-foreground">
                                Diese Allgemeinen Geschäftsbedingungen gelten für alle Verträge zwischen [TODO:
                                Anbietername laut Impressum] (nachfolgend „Anbieter") und Verbrauchern (§ 13 BGB)
                                über die Nutzung des Online-Dienstes Prüfungstrainer (erreichbar unter [TODO: Domain]).
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 2 Vertragsgegenstand</h2>
                            <p className="mt-2 text-muted-foreground">
                                Der Anbieter stellt eine webbasierte Prüfungs- und Lernplattform für die Vorbereitung
                                auf die BSI-Prüfung „IT-Grundschutz-Praktiker" zur Verfügung. Der kostenpflichtige
                                Lifetime-Zugang umfasst:
                            </p>
                            <ul className="mt-2 list-disc pl-6 text-muted-foreground">
                                <li>Unbegrenzte Prüfungssimulationen mit detaillierter Antwort-Erklärung</li>
                                <li>Themenbasierte Auswertung</li>
                                <li>Modus „Freies Lernen" mit Wiederholung falsch beantworteter Fragen</li>
                                <li>Lebenslanger Zugang ohne wiederkehrende Gebühren</li>
                            </ul>
                            <p className="mt-2 text-muted-foreground">
                                Die kostenlose Prüfungssimulation steht ohne Vertragsschluss zur Verfügung.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 3 Vertragsschluss</h2>
                            <p className="mt-2 text-muted-foreground">
                                Der Vertrag kommt durch erfolgreichen Abschluss des Bezahlvorgangs über unseren
                                Zahlungsdienstleister Polar Software, Inc. („Polar.sh") zustande. Der Anbieter
                                bestätigt den Vertragsschluss per E-Mail.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 4 Preise und Zahlung</h2>
                            <p className="mt-2 text-muted-foreground">
                                Der Lifetime-Zugang kostet einmalig den auf der Bestellseite angezeigten Preis
                                (derzeit 29 € im Founder's Price oder 49 € regulär; alle Preise inklusive der jeweils
                                gesetzlichen Umsatzsteuer). Die Zahlung erfolgt über Polar Software, Inc., welche
                                als Merchant of Record auftritt und Rechnungen erstellt.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 5 Widerrufsrecht und Verzicht</h2>
                            <p className="mt-2 text-muted-foreground">
                                Verbraucher haben grundsätzlich ein 14-tägiges Widerrufsrecht. Der Anbieter erbringt
                                die Dienstleistung jedoch unmittelbar nach Vertragsschluss (sofortige Freischaltung
                                des Lifetime-Zugangs). Mit Abschluss des Bezahlvorgangs erklärt der Verbraucher
                                ausdrücklich, dass die Ausführung der Dienstleistung vor Ablauf der Widerrufsfrist
                                beginnt, und bestätigt seine Kenntnis darüber, dass er sein Widerrufsrecht durch
                                vollständige Vertragserfüllung verliert (§ 356 Abs. 5 BGB).
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 6 Verfügbarkeit und Aktualität der Inhalte</h2>
                            <p className="mt-2 text-muted-foreground">
                                Der Anbieter bemüht sich um eine möglichst hohe Verfügbarkeit der Plattform.
                                Eine ununterbrochene Erreichbarkeit wird nicht zugesichert. Die Fragen orientieren
                                sich an der jeweils aktuellen BSI-Prüfungsstruktur; der Anbieter übernimmt jedoch
                                keine Gewähr, dass alle Inhalte stets dem aktuellen Stand entsprechen.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 7 Haftung</h2>
                            <p className="mt-2 text-muted-foreground">
                                Der Anbieter haftet uneingeschränkt für Vorsatz und grobe Fahrlässigkeit. Für
                                leichte Fahrlässigkeit haftet der Anbieter nur bei Verletzung wesentlicher
                                Vertragspflichten (Kardinalpflichten) und der Höhe nach begrenzt auf den
                                vertragstypischen, vorhersehbaren Schaden.
                            </p>
                            <p className="mt-2 text-muted-foreground">
                                Die Plattform dient ausschließlich der Prüfungsvorbereitung. Eine Garantie für das
                                Bestehen der BSI-Prüfung wird nicht übernommen.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 8 Streitbeilegung</h2>
                            <p className="mt-2 text-muted-foreground">
                                Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS)
                                bereit:{' '}
                                <a href="https://ec.europa.eu/consumers/odr" className="underline" target="_blank" rel="noopener noreferrer">
                                    https://ec.europa.eu/consumers/odr
                                </a>
                                . Der Anbieter ist nicht verpflichtet, an Streitbeilegungsverfahren vor einer
                                Verbraucherschlichtungsstelle teilzunehmen, ist hierzu aber grundsätzlich bereit.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 9 Schlussbestimmungen</h2>
                            <p className="mt-2 text-muted-foreground">
                                Es gilt das Recht der Bundesrepublik Deutschland unter Ausschluss des UN-Kaufrechts.
                                Sollten einzelne Bestimmungen unwirksam sein, bleibt die Wirksamkeit der übrigen
                                Bestimmungen unberührt.
                            </p>
                        </section>

            <p className="text-xs text-muted-foreground">
                Stand: [TODO: Datum der letzten Aktualisierung einsetzen]
            </p>
        </LegalLayout>
    );
}
