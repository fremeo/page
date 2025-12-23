{block name="inner_body"}
		<form method="post">
		<input type="hidden" name="D[ACTION]" value='save'>
			<table class="table">
			{foreach from=$D.PAGE.D key="kPAG" item="PAG"}
				<table class="table">
					<tr>
						<td>Active:</td>
						<td>{input p=['name'=>"D[PAGE][D][{$kPAG}][Active]", 'value'=>$PAG.Active, 'type' =>'checkbox']}</td>
					</tr>
					<tr>
						<td>Follow:</td>
						<td>{input p=['name'=>"D[PAGE][D][{$kPAG}][Follow]", 'value'=>$PAG.Follow, 'type' =>'checkbox']} Soll Suchmaschiene die Seite Indizieren? Bei No-follow wird die Seite per JS in base4 verschlüsselt auf der Zielseite ausgegeben. (zusätzlicher Schutz)</td>
					</tr>
				</table>
				Deutsch<br>
				<table class="table">
					<tr>
						<td>Active:</td>
						<td>{input p=['name'=>"D[PAGE][D][{$kPAG}][LANGUAGE][D][DE][Active]", 'value'=>$PAG.LANGUAGE.D['DE'].Active, 'type' =>'checkbox']}</td>
					</tr>
					<tr>
						<td>SEO-URL:</td>
						<td>{input p=['name'=>"D[PAGE][D][{$kPAG}][LANGUAGE][D][DE][FromURL]", 'value'=>$D.LINK.D[ $PAG.LANGUAGE.D['DE'].LINK ].FromURL ]}
							{input p=['name'=>"D[PAGE][D][{$kPAG}][LANGUAGE][D][DE][LINK]", 'value'=>$PAG.LANGUAGE.D['DE'].LINK, 'type' => 'hidden' ]}
						</td>
					</tr>
					<tr>
						<td>Title:</td>
						<td>{input p=['name'=>"D[PAGE][D][{$kPAG}][LANGUAGE][D][DE][Title]", 'value'=>$PAG.LANGUAGE.D['DE'].Title]}</td>
					</tr>
					<tr>
						<td>Text:</td>
						<td>{input p=['name'=>"D[PAGE][D][{$kPAG}][LANGUAGE][D][DE][Text]", 'type'=> 'wysiwyg', 'style'=>'height:300px', 'value'=>str_replace('textarea>','-textarea>',$PAG.LANGUAGE.D['DE'].Text)]}</td>
					</tr>
				</table>
			{/foreach}
			
			<button type="submit" class="btn btn-primary btn-sm">Save</button>
		</form>
	

	{*print_r($PLA.LINK)*}
{/block}