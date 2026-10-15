{** Admin: Seite bearbeiten (Daten: $D.PAGE.D und $D.LINK.D, Meldungen: $D.R.Msg / $D.R.Info aus admin__page.edit.php) **}
{block name="inner_body"}
	{if $D.R.Msg}<div class="alert alert-success">{$D.R.Msg|escape}</div>{/if}
	{foreach $D.R.Info as $info}<div class="alert alert-warning">{$info|escape}</div>{/foreach}
	{literal}
	<script>
		// SEO-URL beim Eingeben des Titels vorschlagen, solange sie nicht selbst geändert wurde (Server normalisiert endgültig, siehe Link::normalize).
		// Das Feld wird dabei als geändert markiert, nur dann sendet das Formular es mit.
		function seoFromTitle(prefix) {
			var map = {'ä':'a','ö':'o','ü':'u','ß':'ss','é':'e','è':'e','ê':'e','à':'a','â':'a','á':'a','ô':'o','ó':'o','û':'u','ú':'u','î':'i','í':'i','ç':'c','ñ':'n'};
			var slug = function(s) {
				s = s.toLowerCase().replace(/[äöüßéèêàâáôóûúîíçñ]/g, function(c) { return map[c]; })
					.replace(/[^a-z0-9\/._-]+/g, '_').replace(/_+/g, '_').replace(/_*\/+_*/g, '/');
				return s.replace(/^[\/_.-]+|[\/_.-]+$/g, '');
			};
			var t = document.getElementsByName(prefix + '[Title]')[0], s = document.getElementsByName(prefix + '[FromURL]')[0];
			if (!t || !s) return;
			var auto = !s.value;
			s.addEventListener('input', function() { auto = !s.value; });
			t.addEventListener('input', function() {
				if (!auto) return;
				s.value = slug(t.value);
				input_onmouseout(s);
			});
		}
	</script>
	{/literal}

	<form method="post">
		<input type="hidden" name="D[ACTION]" value='save'>

		{foreach from=$D.PAGE.D key="kPAG" item="PAG"}
			{$L = $PAG.LANGUAGE.D['DE']}
			{$link = $D.LINK.D[$L.LINK]}
			<h1 class="h4 mb-3"><i class="far fa-file-alt me-1"></i> Seite bearbeiten <small class="text-muted">{$kPAG|escape}</small></h1>

			<table class="table">
				<tr>
					<td style="width:12rem">Active:</td>
					<td>{input p=['name'=>"D[PAGE][D][{$kPAG}][Active]", 'value'=>$PAG.Active, 'type' =>'checkbox']}</td>
				</tr>
				<tr>
					<td>Follow:</td>
					<td>{input p=['name'=>"D[PAGE][D][{$kPAG}][Follow]", 'value'=>$PAG.Follow, 'type' =>'checkbox']} Soll die Suchmaschine die Seite indizieren? Bei No-follow wird der Inhalt per JS in base64 verschlüsselt ausgegeben (zusätzlicher Schutz).</td>
				</tr>
			</table>

			<h2 class="h5 mt-4">Deutsch</h2>
			<table class="table">
				<tr>
					<td style="width:12rem">Active:</td>
					<td>{input p=['name'=>"D[PAGE][D][{$kPAG}][LANGUAGE][D][DE][Active]", 'value'=>$L.Active, 'type' =>'checkbox']}</td>
				</tr>
				<tr>
					<td>SEO-URL:</td>
					<td>
						{input p=['name'=>"D[PAGE][D][{$kPAG}][LANGUAGE][D][DE][FromURL]", 'value'=>$link.FromURL|default:'', 'placeholder'=>'wird aus dem Titel vorgeschlagen']}
						{if $link && !($link.FromURL|default:'')}<span class="badge text-bg-success">Startseite</span>{/if}
						<small class="text-muted d-block">Bei einer neuen Seite wird sie beim Eingeben des Titels vorgeschlagen. Leer speichern = diese Seite ist die Startseite (leere URL). Ist eine URL schon vergeben, wird _1, _2, ... angehängt.</small>
					</td>
				</tr>
				<tr>
					<td>Title:</td>
					<td>{input p=['name'=>"D[PAGE][D][{$kPAG}][LANGUAGE][D][DE][Title]", 'value'=>$L.Title]}</td>
				</tr>
				<tr>
					<td>Text:</td>
					<td>{input p=['name'=>"D[PAGE][D][{$kPAG}][LANGUAGE][D][DE][Text]", 'type'=> 'wysiwyg', 'style'=>'height:300px', 'value'=>str_replace('textarea>','-textarea>',$L.Text)]}</td>
				</tr>
			</table>
			{* nur bei Sprachen ohne Link vorschlagen: eine vorhandene SEO-URL (auch die leere der Startseite) wird nie vom Titel überschrieben *}
			{if !$link}
				<script>seoFromTitle("D[PAGE][D][{$kPAG|escape:'javascript'}][LANGUAGE][D][DE]");</script>
			{/if}
		{/foreach}

		<button type="submit" class="btn btn-primary btn-sm">Speichern</button>
		<a class="btn btn-outline-secondary btn-sm" href="?R[Page]=admin__page.list&R[ModuleId]=fremeo/page">Zur Liste</a>
	</form>
{/block}
