<div>

    <div class="stm_lms_wizard__hint__question" v-bind:class="[{'inactive' : !active}]">
        <i class="fa fa-info"></i>
    </div>

    <div class="stm_lms_wizard__hint" v-bind:class="[{'inactive' : !active}, 'step_' + current_step]">
        <div class="stm_lms_wizard__hint_arr"></div>
        <div class="stm_lms_wizard__hint_section h5">{{steps[current_step]['section']}}</div>
        <div class="stm_lms_wizard__hint_title h2" v-html="steps[current_step]['hint']"></div>
    </div>

    <div class="stm_lms_wizard" v-bind:class="{'inactive' : !active}">
        <div class="stm_lms_wizard__inner">

            <div class="stm_lms_wizard__close" @click="active = !active" v-bind:class="{'closed' : !active}">
                <i class="fa" v-bind:class="{'fa-question' : !active, 'fa-minus' : active}"></i>
            </div>

            <div class="stm-lms-lesson_navigation heading_font">

                <div class="stm-lms-lesson_navigation_side stm-lms-lesson_navigation_prev">
                    <a href="#" v-if="current_step !== 0" @click.prevent="prevStep()">
                        <i class="lnr lnr-chevron-left"></i>
                        <span class="stm_lms_section_text">{{steps[current_step - 1]['section']}}</span>
                        <span v-html="steps[current_step - 1]['title']"></span>
                    </a>
                </div>

                <div class="stm-lms-lesson_navigation_complete">
                    <h5 v-html="steps[current_step]['section']"></h5>
                    <h2 v-html="steps[current_step]['title']"></h2>
                    <div class="wizard-html" v-if="steps[current_step]['html']" v-html="steps[current_step]['html']"></div>
                </div>

                <div class="stm-lms-lesson_navigation_side stm-lms-lesson_navigation_next">
                    <a href="#" v-if="current_step !== (steps.length - 1)" @click.prevent="nextStep()">
                        <span class="stm_lms_section_text">{{steps[current_step + 1]['section']}}</span>
                        <span v-html="steps[current_step + 1]['title']"></span>
                        <i class="lnr lnr-chevron-right"></i>
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>