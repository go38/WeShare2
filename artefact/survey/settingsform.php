<form class="pieform" name="editsurvey" method="post" action="" id="editsurvey">
<table cellspacing="0"><tbody>
	<tr id="editsurvey_title_container" class="required select">
		<th><?php echo $elements['title']['labelhtml']; ?></th>
		<td>
		<select class="required select" id="editsurvey_title" name="title"<?php if ($elements['title']['disabled']) echo ' disabled="disabled"'; ?> onchange="selectSurveyLanguage(document.editsurvey.title[document.editsurvey.title.selectedIndex].value, &quot;language&quot;)" tabindex="1">
			<option value="empty"><?php echo get_string('selectsurvey', 'artefact.survey'); ?></option>
			<?php if (isset($elements['language_surveys']['options']) && !empty($elements['language_surveys']['options'])) { ?>
			<optgroup label="<?php echo get_string('categorylanguage', 'artefact.survey'); ?>">
				<?php foreach ($elements['language_surveys']['options'] as $value => $name) { ?>
					<option value="<?php echo $value; ?>"<?php if ($value == $elements['title']['defaultvalue']) echo " selected"; ?>><?php echo $name; ?></option>
				<?php } ?>
			</optgroup>
			<?php } ?>
			<?php if (isset($elements['personal_surveys']['options']) && !empty($elements['personal_surveys']['options'])) { ?>
			<optgroup label="<?php echo get_string('categorypersonal', 'artefact.survey'); ?>">
				<?php foreach ($elements['personal_surveys']['options'] as $value => $name) { ?>
					<option value="<?php echo $value; ?>"<?php if ($value == $elements['title']['defaultvalue']) echo " selected"; ?>><?php echo $name; ?></option>
				<?php } ?>
			</optgroup>
			<?php } ?>
			<?php if (isset($elements['career_surveys']['options']) && !empty($elements['career_surveys']['options'])) { ?>
			<optgroup label="<?php echo get_string('categorycareer', 'artefact.survey'); ?>">
				<?php foreach ($elements['career_surveys']['options'] as $value => $name) { ?>
					<option value="<?php echo $value; ?>"<?php if ($value == $elements['title']['defaultvalue']) echo " selected"; ?>><?php echo $name; ?></option>
				<?php } ?>
			</optgroup>
			<?php } ?>
			<?php if (isset($elements['other_surveys']['options']) && !empty($elements['other_surveys']['options'])) { ?>
			<optgroup label="<?php echo get_string('categoryother', 'artefact.survey'); ?>">
				<?php foreach ($elements['other_surveys']['options'] as $value => $name) { ?>
					<option value="<?php echo $value; ?>"<?php if ($value == $elements['title']['defaultvalue']) echo " selected"; ?>><?php echo $name; ?></option>
				<?php } ?>
			</optgroup>
			<?php } ?>
			<?php if (isset($elements['staff_surveys']['options']) && !empty($elements['staff_surveys']['options'])) { ?>
			<optgroup label="<?php echo get_string('categorystaff', 'artefact.survey'); ?>">
				<?php foreach ($elements['staff_surveys']['options'] as $value => $name) { ?>
					<option value="<?php echo $value; ?>"<?php if ($value == $elements['title']['defaultvalue']) echo " selected"; ?>><?php echo $name; ?></option>
				<?php } ?>
			</optgroup>
			<?php } ?>
		</select></td>
	</tr>
	<tr>
		<td></td><td class="description"><?php echo $elements['title']['description']; ?></td>
	</tr>
	<tr id="editsurvey_language_container" class="css_select">
		<th><?php echo $elements['language']['title']; ?></th>
		<td><?php echo $elements['language']['html']; ?></td>
	</tr>
	<tr>
		<td></td><td class="description"><?php echo $elements['language']['description']; ?></td>
	</tr>
	<tr id="editsurvey_recipients_container" class="userlist">
		<th><?php echo $elements['recipients']['title']; ?></th>
		<td><?php echo $elements['recipients']['html']; ?></td>
	</tr>
	<tr id="editsurvey_submit_container" class="submitcancel">
		<th></th>
		<td><?php echo $elements['submit']['html']; ?></td>
	</tr>
</tbody></table>
<input type="hidden" class="hidden autofocus" id="editsurvey_id" name="id" value="<?php echo $elements['id']['value']; ?>">
<input type="hidden" class="hidden" id="editsurvey_sesskey" name="sesskey" value="<?php global $USER; echo $USER->get('sesskey'); ?>">
<input type="hidden" name="pieform_editsurvey" value="">
</form>
