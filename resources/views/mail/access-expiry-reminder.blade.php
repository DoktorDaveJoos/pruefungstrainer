<p>Hallo {{ $user->name ?? 'Lernender' }},</p>

<p>dein Zugang zum Prüfungstrainer endet am {{ $expiresAt->translatedFormat('d.m.Y') }}.</p>

<p>Möchtest du ihn verlängern? <a href="{{ url('/') }}">Hier geht's zum Angebot.</a></p>
