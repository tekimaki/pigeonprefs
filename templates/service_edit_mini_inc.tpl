{strip}
{foreach name=pigeonOptions from=$pigeonOptions item=options key=p}
	<div class="row">
		{formlabel label=$pigeonOptionsLabels.$p for="pigeon_options"}
		{if $options|@count ne 0}
			{forminput}
				<select name="pigeon_options[{$p}][]" id="pigeon_options" {*multiple="multiple" size="6" *}>
					<option value="">
						{tr}Select one...{/tr}
					</option>
					{foreach from=$options key=pigeonId item=path}
						<option value="{$pigeonId}" {if $path.selected}selected="selected"{/if}>
							{foreach from=$path item=node}
								{if $node.parent_id} &raquo;{/if} {$node.title|escape}
							{/foreach}
						</option>
					{/foreach}
				</select>
			{/forminput}
		{else}
			{forminput}
				<p>{tr}There are no categories available at the moment.{/tr}</p>
				{if $gBitUser->isAdmin()}
					{smartlink ititle="Create Category" ipackage="pigeon_options" ifile="edit_pigeonholes.php"}
				{/if}
			{/forminput}
		{/if}
	</div>
{/foreach}
{/strip}
