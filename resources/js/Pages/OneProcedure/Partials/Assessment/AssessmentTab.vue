<script setup>
import AssessmentList from './AssessmentList.vue';
import AssessmentCreateForm from './AssessmentCreateForm.vue';
import AssessmentManage from './AssessmentManage.vue';

defineProps({
  assessment: { type: Object, required: true },
  procedures: { type: Array, default: () => [] },
  canManage: { type: Boolean, default: false },
});
</script>

<template>
  <AssessmentManage
    v-if="assessment.manage"
    :assessment="assessment.manage"
    :questions="assessment.questions"
    :stats="assessment.stats"
    :target-roles="assessment.targetRoles"
    :signers="assessment.signers"
    :can-manage="canManage"
  />
  <AssessmentCreateForm
    v-else-if="assessment.createMode"
    :procedures="procedures"
    :target-roles="assessment.targetRoles"
    :proc-id="assessment.procId"
    :focus="assessment.focus"
  />
  <AssessmentList v-else :assessments="assessment.list" :can-manage="canManage" :current-employee-id="assessment.currentEmployeeId" />
</template>
