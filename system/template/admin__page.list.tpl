{block name="inner_body"}
	<form method="post">
		<input type="hidden" name="D[ACTION]" value='save'>
		<button type="button" onclick="window.open('?R[Page]=admin__page.edit&R[ModuleId]=fremeo/page&D[PAGE][D][{hash("crc32b", time())}][Active]=1','_self');" class="btn btn-primary btn-sm">Neu</button>
	<table class="table">
		<thead>
			<tr>
			<th scope="col">#</th>
			<th scope="col">Active</th>
			<th scope="col">Follow</th>
			<th scope="col">Title</th>
			<th scope="col"></th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$D.PAGE.D key="kPAG" item="PAG"}
			<tr>
				<td scope="row">{$kPAG}</td>
				<td scope="row">{input p=['name'=>"D[PAGE][D][{$kPAG}][Active]", 'value'=>$PAG.Active, 'type' => 'checkbox']}</td>
				<td scope="row">{input p=['name'=>"D[PAGE][D][{$kPAG}][Follow]", 'value'=>$PAG.Follow, 'type' => 'checkbox']}</td>
				<td>{$PAG.LANGUAGE.D['DE'].Title}</td>
				<td class="text-end text-nowrap">
					<button type="button" onclick="window.open('?R[Page]=admin__page.edit&R[ModuleId]=fremeo/page&R[Id]={$kPAG}','_self');" class="btn btn-primary btn-sm">Edit</button>
					<input type="hidden" name="D[PAGE][D][{$kPAG}][__DELETE__]" value="1" disabled>
					<button type="button" class="btn btn-outline-danger btn-sm" title="Löschen" data-title="{$PAG.LANGUAGE.D['DE'].Title|default:$kPAG|escape}" onclick="if (confirm('Seite \'' + this.dataset.title + '\' wirklich löschen?\nDas Löschen erfolgt erst beim Speichern.')) { var r = this.closest('tr'); r.querySelector('input[name$=\'[__DELETE__]\']').disabled = false; r.classList.add('d-none'); }"><i class="fa-solid fa-trash"></i></button>
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<button type="submit" class="btn btn-primary btn-sm">Save</button>
	</form>
	
{/block}