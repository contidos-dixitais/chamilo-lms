<template>
  <div>
    <v-card class="mx-auto">
      <CourseForm
        v-if="item"
        ref="updateForm"
        :errors="violations"
        :values="item"
      />
      <Loading :visible="isLoading || deleteLoading" />
      <v-footer>
        <Toolbar
          :handle-delete="del"
          :handle-reset="resetForm"
          :handle-submit="onSendForm"
        />
      </v-footer>
    </v-card>
  </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex"
import { mapFields } from "vuex-map-fields"
import CourseForm from "../../components/course/Form.vue"
import Loading from "../../components/Loading.vue"
import Toolbar from "../../components/Toolbar.vue"
import UpdateMixin from "../../mixins/UpdateMixin"

const servicePrefix = "Course"

export default {
  name: "CourseUpdate",
  servicePrefix,
  mixins: [UpdateMixin],
  components: {
    Loading,
    Toolbar,
    CourseForm,
  },

  computed: {
    ...mapFields("course", {
      deleteLoading: "isLoading",
      isLoading: "isLoading",
      error: "error",
      updated: "updated",
      violations: "violations",
    }),
    ...mapGetters("course", ["find"]),
  },

  methods: {
    ...mapActions("course", {
      createReset: "resetCreate",
      deleteItem: "del",
      delReset: "resetDelete",
      retrieve: "load",
      update: "update",
      updateReset: "resetUpdate",
    }),
  },
}
</script>
