{* file to handle db changes in 4.7.9 during upgrade *}

-- CRM-17607 Change PDF activity type label
SELECT @option_group_id_act := max(id) from civicrm_option_group where name = 'activity_type';
UPDATE civicrm_option_value SET
  {localize field="label"}label = '{ts escape="sql"}Print/Merge Document{/ts}'{/localize},
  {localize field="description"}description = '{ts escape="sql"}Export letters and other printable documents.{/ts}'{/localize}
WHERE name = 'Print PDF Letter' AND option_group_id = @option_group_id_act;

-- CRM-18699 Fix Wake Island misspelling, was Wake Ialand
UPDATE civicrm_state_province SET name="Wake Island" WHERE name="Wake Ialand";

--CRM-12252 Add in help_pre and help_post colmns to price field value table
ALTER TABLE `civicrm_price_field_value`
ADD `help_pre` text COLLATE utf8_unicode_ci COMMENT 'Price field option pre help text.';
ALTER TABLE `civicrm_price_field_value`
ADD `help_post` text COLLATE utf8_unicode_ci COMMENT 'Price field option post field help.';
