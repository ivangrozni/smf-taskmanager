<?php

function template_main()
{
    global $scripturl;

	//if (allowedTo('add_new_todo'))
    echo 'sss, sss, kids! hey, kids!<br> wanna build communism?';
    template_button_strip(array(array('text' => 'delegator_add_task', 'image' => 'to_do_add.gif', 'lang' => true, 'url' => $scripturl . '?action=delegator' . ';sa=add', 'active'=> true)), 'right'); // ';sa=add' - tukaj more biti add
    //template_button_strip(array(array('text' => 'delegator_add', 'image' => 'to_do_add.gif', 'lang' => true, 'url' => $scripturl . '?action=add_task' . ';sa=add', 'active'=> true)), 'right');

    // template_button_strip(array(array('text' => 'delegator_add_proj', 'image' => 'to_do_add.gif', 'lang' => true, 'url' => $scripturl . '?action=delegator' . ';sa=proj', 'active'=> true)), 'right');
    template_button_strip(array(array('text' => 'delegator_add_proj', 'image' => 'to_do_add.gif', 'lang' => true, 'url' => $scripturl . '?action=delegator' . ';sa=proj', 'active'=> true)), 'right');

    template_show_list('list_tasks');
                //template_show_list('list_delegator');
}

function template_add()
{
	global $scripturl, $context, $txt;
        // id_author, name, description, creation_date, deadline, priority, state

        // dobiti moram projekte: // vir: http://wiki.simplemachines.org/smf/Db_query
        global $smcFunc;
        $request = $smcFunc['db_query']('', ' 
                 SELECT id, name 
                 FROM  {db_prefix}projects  ', array()  ); // pred array je manjkala vejica in je sel cel forum v k
        // Zgoraj je treba querry tako popravit, da bo prikazoval se ne zakljucene projekte (POGOJ danasnji datum je pred koncem projekta)
	echo '
	<div id="container">
		<h3 class="catbg"><span class="left"></span>
			', $context['page_title'], '
		</h3>
                                           <!-- tukaj bom probal dat sa=add_task -->
		<form action="', $scripturl, '?action=delegator;sa=add_task" method="post" accept-charset="', $context['character_set'], '" name="delegator_add">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
					<dl class="delegator_add">
						<dt>
						<!--	<label for="name">', $txt['task_name'], '</label> --> <!-- pusti prazno, ker nimamo definiranega texta tas_name -->
                                   <label for="name"> Zadolzitev </label> 
						</dt>
						<dd>
							<input type="text" name="name" value="" size="50" maxlength="255" class="input_text" />
						</dd>
                                                <dt>
              <!-- <label for="description>"', $txt['task_desc'],' </label> -->
                    <label for="description>" opis:  </label>
 </dt>
                               <dd>
           <!-- <input type="text" name="description" value="" ROW=3 COL=30 maxlength="250" class="input_text" /> -->
                <textarea name="description" rows="3" cols="30"> </textarea>
                                </dd>
						<dt>
							<label for="duet3">', $txt['delegator_deadline'], '</label><br />
							<span class="smalltext">', $txt['delegator_due_year'], ' - ', $txt['delegator_due_month'], ' - ', $txt['delegator_due_day'], '</span>
						</dt>
						<dd>
							<input type="text" name="duet3" size="4" maxlength="4" value="" class="input_text" /> -
							<input type="text" name="duet1" size="2" maxlength="2" value="" class="input_text" /> -
							<input type="text" name="duet2" size="2" maxlength="2" value="" class="input_text" />
						</dd>
						<dt>
							<label for="user">Delegirani uporabniki</label>
						</dt>
						<dd>
							<input type="text" name="user">
							<div id="user-list"></div>
						</dd>
						<dt>
							<label>', $txt['delegator_priority'], '</label>
						</dt>
						<dd>
							<ul class="reset">
								<li><input type="radio" name="priority" id="priority_0" value="0" class="input_radio" class="input_radio" /> ', $txt['delegator_priority0'], '</li>
								<li><input type="radio" name="priority" id="priority_1" value="1" class="input_radio" class="input_radio" checked="checked" /> ', $txt['delegator_priority1'], '</li>
								<li><input type="radio" name="priority" id="priority_2" value="2" class="input_radio" class="input_radio" /> ', $txt['delegator_priority2'], '</li>
							</ul>
						</dd>
					</dl>

                                         <dt>
							<label>', $txt['project_name'], '</label>
					 </dt>
						<dd>
						
                       <select name="id_proj">'; // nadomestil navadno vejico
        while ($row = $smcFunc['db_fetch_assoc']($request)) {
            echo '<option value="'.$row['id'].'">'.$row['name'].'</option> ';
            }
        $smcFunc['db_free_result']($request);

            echo ' </select> 

						</dd>
					</dl>


					<div id="confirm_buttons">
						<input type="submit" name="submit" value="', $txt['delegator_add_proj'], '" class="button_submit" />
					</div>
			</div>
			<span class="botslice"><span></span></span>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</div>
		</form>
		<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/suggest.js?fin20"></script>
		<script type="text/javascript"><!-- // --><![CDATA[
			var oAddMemberSuggest = new smc_AutoSuggest({
				sSelf: \'oAddMemberSuggest\',
				sSessionId: \'', $context['session_id'], '\',
				sSessionVar: \'', $context['session_var'], '\',
				sSuggestId: \'user\',
				sControlId: \'toAdd\',
				sSearchType: \'member\',
				sPostName: \'member_add\',
				sURLMask: \'action=profile;u=%item_id%\',
				sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
				bItemList: true,
				sItemListContainerId: \'user-list\'
			});
		// ]]></script>
	</div><br />';
}

//funkcija za dodajanje projektov
//imena morajo ustrezat subactionnom...
function template_proj()
{
	global $scripturl, $context, $txt;

	echo '
	<div id="container">
		<h3 class="catbg"><span class="left"></span>
			', $context['page_title'], '
		</h3>
		<form action="', $scripturl, '?action=delegator;sa=add_proj" method="post" accept-charset="', $context['character_set'], '" name="delegator_proj">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
					<dl class="delegator_add_proj">
						<dt>
							<label for="name">', $txt['project_name'], '</label>
						</dt>
						<dd>
							<input type="text" name="name" value="" size="50" maxlength="255" class="input_text" />
						</dd>

						<dt>
							<label for="description">', $txt['project_desc'], '</label>
						</dt>
						<dd>
						<textarea name="description" rows="3" cols="30"> </textarea>
						</dd>



<dt>
							<label for="start">', $txt['delegator_deadline'], '</label><br />
							<span class="smalltext">', $txt['xdelegator_due_year'], ' - ', $txt['delegator_due_month'], ' - ', $txt['delegator_due_day'], '</span>
						</dt>
						<dd>
							<input type="text" name="duet3" size="4" maxlength="4" value="" class="input_text" /> -
							<input type="text" name="duet1" size="2" maxlength="2" value="" class="input_text" /> -
							<input type="text" name="duet2" size="2" maxlength="2" value="" class="input_text" />
						</dd>

<dt>
							<label for="end">', $txt['delegator_deadline'], '</label><br />
							<span class="smalltext">', $txt['delegator_due_year'], ' - ', $txt['delegator_due_month'], ' - ', $txt['delegator_due_day'], '</span>
						</dt>
						<dd>
							<input type="text" name="dend3" size="4" maxlength="4" value="" class="input_text" /> -
							<input type="text" name="dend1" size="2" maxlength="2" value="" class="input_text" /> -
							<input type="text" name="dend2" size="2" maxlength="2" value="" class="input_text" />
						</dd>						



					</dl>
					<div id="confirm_buttons">
						<input type="submit" name="submit" value="', $txt['delegator_add_task'], '" class="button_submit" />
					</div>
			</div>
			<span class="botslice"><span></span></span>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</div>
		</form>
	</div><br />';
}

function template_view_task() // id bi bil kar dober argument
{
    /*
      Imena zadolzitev je treba spremeniti v linke, id se lahko posreduje z metodo GET
      Iz baze moram potegniti podatke o tasku
      Prikazati: Ime, rok, pomembnost
                 Opis, opis, opis, opis, opis
                 Izvajalce...
      2 gumba: Back in Submit (nazaj in sprejmi zadolzitev)
     */
}


?>