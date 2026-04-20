import LegalLayout from '@/layouts/legal-layout';

export default function Impressum() {
    return (
        <LegalLayout title="Impressum" headTitle="Impressum · Prüfungstrainer">
            <section>
                            <h2 className="text-lg font-semibold">Angaben gemäß § 5 TMG</h2>
                            <p className="mt-2 text-muted-foreground">
                                David Joos
                                <br />
                                Tobel 30
                                <br />
                                88285 Bodnegg
                                <br />
                                Deutschland
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">Kontakt</h2>
                            <p className="mt-2 text-muted-foreground">
                                E-Mail:{' '}
                                <a href="mailto:info@davidjoos.de" className="underline">
                                    info@davidjoos.de
                                </a>
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">Umsatzsteuer</h2>
                            <p className="mt-2 text-muted-foreground">
                                Gemäß § 19 UStG (Kleinunternehmerregelung) wird keine Umsatzsteuer
                                berechnet und folglich nicht in Rechnungen ausgewiesen.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV</h2>
                            <p className="mt-2 text-muted-foreground">
                                David Joos, Tobel 30, 88285 Bodnegg
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">EU-Streitschlichtung</h2>
                            <p className="mt-2 text-muted-foreground">
                                Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS)
                                bereit:{' '}
                                <a href="https://ec.europa.eu/consumers/odr" className="underline" target="_blank" rel="noopener noreferrer">
                                    https://ec.europa.eu/consumers/odr
                                </a>
                                . Unsere E-Mail-Adresse finden Sie oben im Impressum.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">Verbraucherstreitbeilegung / Universalschlichtungsstelle</h2>
                            <p className="mt-2 text-muted-foreground">
                                Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer
                                Verbraucherschlichtungsstelle teilzunehmen.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">Haftung für Inhalte</h2>
                            <p className="mt-2 text-muted-foreground">
                                Als Diensteanbieter sind wir gemäß § 7 Abs. 1 TMG für eigene Inhalte auf diesen
                                Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir
                                jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu
                                überwachen.
                            </p>
                            <p className="mt-2 text-muted-foreground">
                                Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den
                                allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung ist
                    jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich.
                </p>
            </section>
        </LegalLayout>
    );
}
