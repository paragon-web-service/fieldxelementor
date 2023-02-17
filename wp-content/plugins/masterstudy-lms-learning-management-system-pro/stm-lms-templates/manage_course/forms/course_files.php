<div class="stm_metaboxes_grid">
    <div class="stm_metaboxes_grid__inner">
        <div id="stm_lms_add_new_section_materials">
            <wpcfto_repeater v-bind:fields="course_file_pack_data"
                             v-bind:parent_repeater="'parent'"
                             v-bind:field_label="course_file_pack_data['label']"
                             v-bind:field_name="'course_files_pack'"
                             v-bind:field_id="'section_settings-course_files_pack'"
                             v-bind:field_value="fields['course_files_pack']"
                             @wpcfto-get-value="$set(fields, 'course_files_pack', JSON.stringify($event));">
            </wpcfto_repeater>
        </div>
    </div>
</div>