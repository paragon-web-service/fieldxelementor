<div>

    <div class="stm_lms_item_modal__backdrop"
         @click="opened = false"
         v-bind:class="{'opened' : opened}">
    </div>

    <div class="stm_lms_item_modal" v-bind:class="{'opened' : opened}">

        <stm-lesson v-show="post_type=='stm-lessons'"></stm-lesson>
        <stm-assignment v-show="post_type=='stm-assignments'"></stm-assignment>
        <stm-quiz v-show="post_type=='stm-quizzes'"></stm-quiz>

    </div>

</div>