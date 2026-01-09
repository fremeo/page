{block name="inner_body" prepend}
	{foreach from=$D.PAGE.D key="kPAG" item="PAG"}
		
		<meta name="robots" content="{if $PAG.Follow}index, follow{else}noindex, nofollow{/if}">
		<div class="card mb-4 bg-light border-0">
			<div class="card-body p-9">
				<h1 class="mb-0 fs-1">{if $PAG.Follow}{$PAG.LANGUAGE.D['DE'].Title}{else}
				<div id='t{$kPAG}'>Please enable JavaScript to view this content.<script>$('#t{$kPAG}').html(atob('{base64_encode(mb_convert_encoding($PAG.LANGUAGE.D['DE'].Title, 'Windows-1252','UTF-8'))}'));</script></div>
				{/if}</h1>
			</div>
		</div>
		
	{if $PAG.Follow}{$PAG.LANGUAGE.D['DE'].Text}{else}
		<div id='c{$kPAG}'>Please enable JavaScript to view this content.<script>$('#c{$kPAG}').html(atob('{base64_encode(mb_convert_encoding($PAG.LANGUAGE.D['DE'].Text, 'Windows-1252','UTF-8'))}'));</script></div>
	{/if}
	{/foreach}
{/block}