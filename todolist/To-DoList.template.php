<?php

function template_main()
{
	global $scripturl;

	if (allowedTo('add_new_todo'))
		template_button_strip(array(array('text' => 'to_do_add', 'image' => 'to_do_add.gif', 'lang' => true, 'url' => $scripturl . '?action=todo' . ';sa=add', 'active'=> true)), 'right');

	template_show_list('list_todos');
}

function template_add()
{
	global $scripturl, $context, $txt;

	echo '
	<div id="container">
		<h3 class="catbg"><span class="left"></span>
			', $context['page_title'], '
		</h3>
		<form action="', $scripturl, '?action=todo;sa=add2" method="post" accept-charset="', $context['character_set'], '" name="todo_add">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
					<dl class="todo_add">
						<dt>
							<label for="subject">', $txt['to_do_subject'], '</label>
						</dt>
						<dd>
							<input type="text" name="subject" value="" size="50" maxlength="255" class="input_text" />
						</dd>
						<dt>
							<label for="duet3">', $txt['to_do_due_time'], '</label><br />
							<span class="smalltext">', $txt['to_do_due_year'], ' - ', $txt['to_do_due_month'], ' - ', $txt['to_do_due_day'], '</span>
						</dt>
						<dd>
							<input type="text" name="duet3" size="4" maxlength="4" value="" class="input_text" /> -
							<input type="text" name="duet1" size="2" maxlength="2" value="" class="input_text" /> -
							<input type="text" name="duet2" size="2" maxlength="2" value="" class="input_text" />
						</dd>
						<dt>
							<label>', $txt['to_do_priority'], '</label>
						</dt>
						<dd>
							<ul class="reset">
								<li><input type="radio" name="priority" id="priority_0" value="0" class="input_radio" class="input_radio" /> ', $txt['to_do_priority0'], '</li>
								<li><input type="radio" name="priority" id="priority_1" value="1" class="input_radio" class="input_radio" checked="checked" /> ', $txt['to_do_priority1'], '</li>
								<li><input type="radio" name="priority" id="priority_2" value="2" class="input_radio" class="input_radio" /> ', $txt['to_do_priority2'], '</li>
							</ul>
						</dd>
					</dl>
					<div id="confirm_buttons">
						<input type="submit" name="submit" value="', $txt['to_do_add'], '" class="button_submit" />
					</div>
			</div>
			<span class="botslice"><span></span></span>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</div>
		</form>
	</div><br />';
}
?>