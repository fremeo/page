{block name="inner_body"}
	<form method="post">
		<input type="hidden" name="D[ACTION]" value='save'>
		<button type="button" onclick="window.open('?R[Page]=admin__page.edit&R[ModulId]=papp/page&D[PAGE][D][{hash("crc32b", time())}][Active]=1','_self');" class="btn btn-primary btn-sm">Neu</button>
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
				<td><button type="button" onclick="window.open('?R[Page]=admin__page.edit&R[ModuleId]=papp/page&D[_ID]={$kPAG}','_self');" class="btn btn-primary btn-sm">Edit</button></td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<button type="submit" class="btn btn-primary btn-sm">Save</button>
	</form>
	
{/block}