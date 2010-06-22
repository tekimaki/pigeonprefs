{strip}
<h2>{tr}Set Contact Service Preferences{/tr}</h2>
{if !$gBitSystem->isPackageActive( 'lcconfig' )}
	{tr}You can customize the LCContact service options to assocaite different meta data with different content types. However, to do so you must have the LCConfig package installed. The LCConfig package currently does not appear to be installed.{/tr}
{else}
	{formfeedback hash=$feedback}
	{form}
		<input type="hidden" name="page" value="{$page}" />
		<table class="data">
			<caption>{tr}Available Content Types{/tr}</caption>
			{foreach from=$gLibertySystem->mContentTypes item=ctype key=p name=ctypes}
				{if $prev_package != $ctype.handler_package}
					<tr>
						<th class="alignleft">{tr}Package{/tr} - {$ctype.handler_package|ucfirst}</th>
						{foreach name=pigeonIds from=$pigeonRootContentIds item=pigeon}
							<th class="width25p">
								{$pigeon.title}
							</th>
						{/foreach}
					</tr>
					{assign var=prev_package value=$ctype.handler_package}
				{/if}
				<tr class="{cycle values="odd,even"}">
					<td title="{$p}">{$gLibertySystem->getContentTypeName($ctype.content_type_guid)}</td>
					{foreach name=pigeonIds from=$pigeonRootContentIds item=pigeon}
						{assign var=config_key value=service_pigeon_content_id_`$pigeon.content_id`}
						<td class="aligncenter">
							<input id="{$p}_{$pigeon.content_id}" type="checkbox" value="{$p}" name="pigeon_ids[{$pigeon.content_id}][{$p}]" title="{$pigeon.title}" {if $LCConfigSettings.$p.$config_key}checked="checked"{/if}/>
						</td>
					{/foreach}
				</tr>
			{/foreach}
		</table>

		<div class="submit">
			<input type="submit" name="save" value="{tr}Apply Changes{/tr}" />
		</div>
	{/form}
{/if}
{/strip}
