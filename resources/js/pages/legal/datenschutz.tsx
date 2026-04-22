import LegalLayout from '@/layouts/legal-layout';

export default function Datenschutz() {
    return (
        <LegalLayout
            title="Datenschutzerklärung"
            headTitle="Datenschutz · Prüfungstrainer"
        >
            <section>
                <h2 className="text-lg font-semibold">1. Verantwortlicher</h2>
                <p className="mt-2 text-muted-foreground">
                    Verantwortlicher im Sinne der DSGVO ist David Joos, Tobel
                    30, 88285 Bodnegg, Deutschland. Kontakt:{' '}
                    <a href="mailto:info@davidjoos.de" className="underline">
                        info@davidjoos.de
                    </a>
                    .
                </p>
            </section>

            <section>
                <h2 className="text-lg font-semibold">
                    2. Welche Daten werden verarbeitet
                </h2>
                <p className="mt-2 text-muted-foreground">
                    <strong>Anonyme Nutzung:</strong> Bei der kostenlosen
                    Prüfungssimulation wird ein technisch notwendiges Cookie (
                    <code>pt_exam_session</code>) gesetzt, das einer anonymen
                    Sitzung den jeweiligen Prüfungsversuch zuordnet. Der Cookie
                    enthält ausschließlich eine zufällige UUID und ist 24
                    Stunden gültig. Es werden keine personenbezogenen Daten
                    erfasst.
                </p>
                <p className="mt-2 text-muted-foreground">
                    <strong>Bei kostenpflichtigem Erwerb:</strong> Im Rahmen des
                    Bezahlvorgangs übermittelt unser Zahlungsdienstleister Polar
                    Software, Inc. (siehe Punkt 4) den Namen und die
                    E-Mail-Adresse des Kunden an uns. Wir legen damit ein
                    Nutzerkonto an und verknüpfen es mit dem ggf. zuvor
                    erfassten anonymen Prüfungsversuch.
                </p>
                <p className="mt-2 text-muted-foreground">
                    <strong>Bei Nutzung als angemeldeter Kunde:</strong> Zur
                    Erbringung der Lerndienstleistung speichern wir pro Frage
                    Antwort, Antwortzeitpunkt und Bewertung (richtig/falsch).
                    Diese Daten dienen ausschließlich der Bereitstellung der
                    Funktionen „Themen-Auswertung" und „Freies Lernen".
                </p>
            </section>

            <section>
                <h2 className="text-lg font-semibold">3. Rechtsgrundlagen</h2>
                <ul className="mt-2 list-disc pl-6 text-muted-foreground">
                    <li>
                        Anonyme Nutzung: Art. 6 Abs. 1 lit. f DSGVO
                        (berechtigtes Interesse — technisch notwendige
                        Sitzungsverwaltung).
                    </li>
                    <li>
                        Vertragsabwicklung: Art. 6 Abs. 1 lit. b DSGVO
                        (Erfüllung des Vertrages über den Lifetime-Zugang).
                    </li>
                    <li>
                        Steuer- und handelsrechtliche Aufbewahrung: Art. 6 Abs.
                        1 lit. c DSGVO i.V.m. § 257 HGB / § 147 AO.
                    </li>
                </ul>
            </section>

            <section>
                <h2 className="text-lg font-semibold">
                    4. Empfänger / Auftragsverarbeiter
                </h2>
                <ul className="mt-2 list-disc pl-6 text-muted-foreground">
                    <li>
                        <strong>Polar Software, Inc.</strong> —
                        Zahlungsabwicklung als Merchant of Record. Erhält
                        Rechnungs- und Zahlungsdaten direkt vom Kunden.
                        Datenschutzerklärung:{' '}
                        <a
                            href="https://polar.sh/legal/privacy"
                            className="underline"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            polar.sh/legal/privacy
                        </a>
                    </li>
                    <li>
                        <strong>Hetzner Online GmbH</strong>, Industriestr. 25,
                        91710 Gunzenhausen — Server-Hosting in Deutschland.
                        Auftragsverarbeitungsvertrag (AVV) liegt vor.
                    </li>
                    <li>
                        <strong>Laravel LLC</strong> (Laravel Forge) —
                        Server-Provisionierung und -Verwaltung der bei Hetzner
                        gehosteten Infrastruktur.
                    </li>
                </ul>
            </section>

            <section>
                <h2 className="text-lg font-semibold">5. Speicherdauer</h2>
                <p className="mt-2 text-muted-foreground">
                    Anonyme Sitzungsdaten: 24 Stunden. Nutzerkonten und
                    Lernfortschritt: bis zur Löschung des Nutzerkontos durch den
                    Kunden. Steuerrelevante Daten: 10 Jahre gemäß § 257 HGB / §
                    147 AO.
                </p>
            </section>

            <section>
                <h2 className="text-lg font-semibold">6. Ihre Rechte</h2>
                <p className="mt-2 text-muted-foreground">
                    Sie haben das Recht auf Auskunft (Art. 15 DSGVO),
                    Berichtigung (Art. 16 DSGVO), Löschung (Art. 17 DSGVO),
                    Einschränkung der Verarbeitung (Art. 18 DSGVO),
                    Datenübertragbarkeit (Art. 20 DSGVO) sowie Widerspruch (Art.
                    21 DSGVO). Anfragen richten Sie bitte an die unter Punkt 1
                    genannte Kontaktadresse.
                </p>
                <p className="mt-2 text-muted-foreground">
                    Sie haben außerdem das Recht, sich bei einer
                    Aufsichtsbehörde zu beschweren — in Deutschland in der Regel
                    bei der Datenschutzaufsicht des Bundeslandes des
                    Verantwortlichen.
                </p>
            </section>

            <section>
                <h2 className="text-lg font-semibold">7. Cookies</h2>
                <p className="mt-2 text-muted-foreground">
                    Wir verwenden ausschließlich technisch notwendige Cookies.
                    Eine Einwilligung gemäß § 25 TTDSG ist hierfür nicht
                    erforderlich. Aktuell werden folgende Cookies gesetzt:
                </p>
                <ul className="mt-2 list-disc pl-6 text-muted-foreground">
                    <li>
                        <code>pt_exam_session</code> — Zufalls-UUID zur
                        Zuordnung anonymer Prüfungsversuche, gültig 24 Stunden.
                    </li>
                    <li>
                        <code>laravel_session</code>, <code>XSRF-TOKEN</code> —
                        Standard-Sitzungs- und CSRF-Cookies des
                        Laravel-Frameworks.
                    </li>
                </ul>
            </section>

            <section>
                <h2 className="text-lg font-semibold">
                    8. Anonyme Nutzungsstatistik
                </h2>
                <p className="mt-2 text-muted-foreground">
                    Wir erfassen anonyme Zugriffe auf unsere Seiten, um zu
                    verstehen, welche Inhalte aufgerufen werden. Dabei wird kein
                    Cookie gesetzt und keine personenbezogene Kennung dauerhaft
                    gespeichert. Als Besucher-Kennung dient ein täglich
                    rotierender SHA-256-Hash aus IP-Adresse, User-Agent, einem
                    serverseitigen Geheimnis und dem aktuellen Datum. Rohdaten
                    werden nach 90 Tagen automatisch gelöscht. Die Erfassung
                    erfolgt auf Grundlage unseres berechtigten Interesses an
                    einer stabilen, kostenfrei für Besucher nutzbaren Anwendung
                    (Art. 6 Abs. 1 lit. f DSGVO).
                </p>
            </section>

            <p className="text-xs text-muted-foreground">
                Stand: 20. April 2026
            </p>
        </LegalLayout>
    );
}
