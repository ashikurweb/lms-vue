<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Premium Dashboard Header Component -->
    <PageHeader 
      title="Student Directory"
      subtitle="Manage and monitor student engagement and progress."
      v-model="search"
      add-label="Add Student"
      search-placeholder="Search students..."
      @search="debounceSearch"
    >
      <template #icon>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      </template>
      <template #actions>
        <button 
          @click="openAddModal"
          class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/20 active:scale-95 transition-all"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span class="hidden sm:inline">Add Student</span>
        </button>
      </template>
    </PageHeader>

    <!-- Dynamic DataTable Engine -->
    <DataTable 
      :headers="tableHeaders"
      :items="students"
      :loading="loading"
      :pagination="pagination"
      empty-title="No students found"
      empty-message="You don't have any students registered yet. They will appear here once they sign up or you add them manually."
      @page-change="fetchStudents"
    >
      <template #row="{ item: student, index }">
        <!-- Persistent Row Styling based on Status -->
        <div 
          v-if="student.status !== 'active'" 
          class="absolute inset-0 bg-rose-500/[0.04] border border-rose-500/20 rounded-3xl pointer-events-none transition-all duration-500"
        ></div>
        
        <div 
          class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none rounded-3xl"
          :class="student.status === 'active' ? 'bg-linear-to-r from-emerald-500/[0.03] via-transparent to-transparent' : ''"
        ></div>

        <!-- SL Column -->
        <TableSLCell :index="formatSL(index)" />

        <!-- Identity Column (Matches Identity Header: col-span-4) -->
        <div class="col-span-4">
          <div v-if="student.isEditing" class="space-y-3">
            <FormInput 
              v-model="student.editForm.name"
              placeholder="Enter full name"
              class="text-sm"
            />
            <FormInput 
              v-model="student.editForm.email"
              type="email"
              placeholder="Enter email address"
              class="text-sm"
            />
            <div class="flex items-center gap-2 text-xs text-slate-500">
              <span class="w-1 h-1 bg-slate-700/50 rounded-full"></span>
              <span class="font-black uppercase tracking-widest">@{{ student.username }}</span>
            </div>
          </div>
          <div v-else>
            <TableIdentityCell 
              :title="student.name"
              :subtitle="student.email"
              :image="student.avatar"
              :to="{ name: 'users.show', params: { uuid: student.uuid } }"
            >
              <template #metadata>
                <span class="w-1 h-1 bg-slate-700/50 rounded-full"></span>
                <span class="text-[9px] font-black theme-text-dim/60 uppercase tracking-widest">@{{ student.username }}</span>
              </template>
            </TableIdentityCell>
          </div>
        </div>

        <!-- Stats Column (Matches Impact Header: col-span-2) -->
        <div class="col-span-2 flex justify-center">
            <TableStatsCell 
              :value="student.courses_count || 0"
              label="Courses"
            />
        </div>

        <!-- Status Column (Matches Status Header: col-span-1) -->
        <div class="col-span-1 flex justify-center">
            <TableToggleCell 
              :enabled="student.status === 'active'"
              :label="student.status === 'active' ? 'Active' : 'Suspended'"
              :active-class="'bg-emerald-500 border-emerald-400 shadow-emerald-900/40'"
              @toggle="handleStatusToggle(student)"
            />
        </div>

        <!-- Enrolled Column -->
        <div class="col-span-2">
            <div class="flex flex-col">
              <span class="text-[11px] font-black theme-text-main tracking-tight">{{ student.created_at }}</span>
              <span class="text-[9px] theme-text-dim font-bold uppercase tracking-widest opacity-60">Join Date</span>
            </div>
        </div>

        <!-- Actions Column -->
        <div class="col-span-2 text-right">
          <div class="flex items-center justify-end gap-1">
            <!-- Edit Button -->
            <button 
              v-if="!student.isEditing"
              @click="startEditing(student)"
              class="w-9 h-9 flex items-center justify-center rounded-xl text-amber-500 hover:bg-amber-500/10 transition-all active:scale-90"
              title="Edit User"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </button>

            <!-- Save Button -->
            <button 
              v-if="student.isEditing"
              @click="saveUser(student)"
              class="w-9 h-9 flex items-center justify-center rounded-xl text-emerald-500 hover:bg-emerald-500/10 transition-all active:scale-90"
              title="Save Changes"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </button>

            <!-- Cancel Button -->
            <button 
              v-if="student.isEditing"
              @click="cancelEditing(student)"
              class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all active:scale-90"
              title="Cancel Editing"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>

            <TableActionDock>
                <router-link 
                  :to="{ name: 'users.show', params: { uuid: student.uuid } }"
                  class="w-9 h-9 flex items-center justify-center rounded-xl text-indigo-500 hover:bg-indigo-500/10 transition-all active:scale-90"
                  title="View Profile"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </router-link>
                <div class="w-px h-4 bg-slate-700/50"></div>
                <button 
                  @click="editStudent(student)"
                  class="w-9 h-9 flex items-center justify-center rounded-xl text-amber-500 hover:bg-amber-500/10 transition-all active:scale-90"
                  title="Edit Student"
                >
                  <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <div class="w-px h-4 bg-slate-700/50"></div>
                <button 
                  @click="triggerDelete(student)"
                  class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all active:scale-90"
                  title="Delete Student"
                >
                  <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
            </TableActionDock>
          </div>
        </div>
      </template>
    </DataTable>

    <!-- Slide-over for Add/Edit -->
    <transition name="panel">
      <div v-if="showModal" class="fixed inset-0 z-100 overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal"></div>
        
        <div class="absolute inset-y-0 right-0 w-full max-w-md theme-bg-card border-l theme-border shadow-2xl flex flex-col">
          <div class="p-8 border-b theme-border flex items-center justify-between">
            <div>
              <h2 class="text-xl font-black theme-text-main tracking-tight">{{ isEditing ? 'Edit' : 'Add' }} Student</h2>
              <p class="text-xs theme-text-dim font-medium">{{ isEditing ? 'Update student account details' : 'Manually register a new student' }}</p>
            </div>
            <button @click="closeModal" class="p-2 theme-text-muted hover:theme-text-main transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            <form @submit.prevent="saveStudent" class="space-y-6">
              
              <div class="flex justify-center pb-4">
                <ImageUploader 
                  v-model="form.avatar"
                  label="Profile Picture"
                  :preview="form.avatar"
                />
              </div>

              <FormInput 
                v-model="form.name"
                label="Full Name"
                placeholder="e.g. John Doe"
                :error="errors.name"
                required
              />

              <FormInput 
                v-model="form.username"
                label="Username"
                placeholder="e.g. johndoe"
                :error="errors.username"
                required
              />

              <FormInput 
                v-model="form.email"
                label="Email Address"
                type="email"
                placeholder="john@example.com"
                :error="errors.email"
                required
              />

              <FormInput 
                v-model="form.phone"
                label="Phone Number"
                placeholder="+1 234 567 890"
                :error="errors.phone"
              />

              <FormInput 
                v-model="form.password"
                label="Account Password"
                type="password"
                :placeholder="isEditing ? 'Leave blank to keep current' : 'Enter strong password'"
                :error="errors.password"
                :required="!isEditing"
              />

              <div class="pt-4">
                <button 
                  type="submit" 
                  :disabled="saving"
                  class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl font-bold shadow-lg shadow-indigo-600/20 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                >
                  <svg v-if="saving" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  <span>{{ isEditing ? 'Update Profile' : 'Create Student' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <!-- Delete Confirmation Dialog -->
    <ActionDialog
      :show="showDeleteModal"
      title="Delete Student Permanently"
      :message="`Be careful! You are about to permanently delete '${studentToDelete?.name}'. This action cannot be undone and will remove all their enrollments and data.`"
      :loading="deleting"
      variant="danger"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { userService } from '../../../services/userService';
import PageHeader from '../../../components/common/PageHeader.vue';
import DataTable from '../../../components/common/DataTable.vue';
import TableActionDock from '../../../components/common/TableActionDock.vue';
import TableSLCell from '../../../components/common/table/TableSLCell.vue';
import TableIdentityCell from '../../../components/common/table/TableIdentityCell.vue';
import TableStatsCell from '../../../components/common/table/TableStatsCell.vue';
import TableToggleCell from '../../../components/common/table/TableToggleCell.vue';
import TableBadgeCell from '../../../components/common/table/TableBadgeCell.vue';
import FormInput from '../../../components/common/FormInput.vue';
import ActionDialog from '../../../components/common/ActionDialog.vue';
import ImageUploader from '../../../components/common/ImageUploader.vue';
import { useToast } from '../../../composables/useToast';

const toast = useToast();

const tableHeaders = [
  { label: 'SL', span: 1, align: 'left' },
  { label: 'Student Identity', span: 4, align: 'left' },
  { label: 'Impact', span: 2, align: 'center' },
  { label: 'Status', span: 1, align: 'center' },
  { label: 'Member Since', span: 2, align: 'left' },
  { label: 'Actions', span: 2, align: 'right' },
];

const students = ref([]);
const loading = ref(true);
const search = ref('');
const showModal = ref(false);
const isEditing = ref(false);
const saving = ref(false);
const toggling = ref(false);
const deleting = ref(false);
const showStatusModal = ref(false);
const showDeleteModal = ref(false);
const studentToToggle = ref(null);
const studentToDelete = ref(null);
const selectedId = ref(null);

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const form = reactive({
  name: '',
  username: '',
  email: '',
  phone: '',
  password: '',
  avatar: null,
});

const errors = ref({});

const fetchStudents = async (page = 1) => {
  loading.value = true;
  try {
    const data = await userService.index({ 
      page, 
      search: search.value,
      per_page: pagination.per_page 
    });
    students.value = data.data;
    const meta = data.meta;
    pagination.current_page = meta.current_page;
    pagination.last_page = meta.last_page;
    pagination.total = meta.total;
  } catch (error) {
    toast.error('Failed to fetch students');
  } finally {
    loading.value = false;
  }
};

const formatSL = (index) => {
  const sl = (pagination.current_page - 1) * pagination.per_page + index + 1;
  return sl < 10 ? `0${sl}` : sl;
};

let debounceTimer;
const debounceSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchStudents(1);
  }, 500);
};

const openAddModal = () => {
  isEditing.value = false;
  selectedId.value = null;
  resetForm();
  showModal.value = true;
};

const editStudent = (student) => {
  if (student.roles?.some(role => ['admin', 'super-admin'].includes(role))) {
    toast.error('Administrative accounts cannot be managed from Student Directory');
    return;
  }
  isEditing.value = true;
  selectedId.value = student.uuid;
  Object.assign(form, {
    name: student.name,
    username: student.username,
    email: student.email,
    phone: student.phone || '',
    password: '',
    avatar: student.avatar,
  });
  showModal.value = true;
};

const resetForm = () => {
  Object.assign(form, {
    name: '',
    username: '',
    email: '',
    phone: '',
    password: '',
    avatar: null,
  });
  errors.value = {};
};

const closeModal = () => {
  showModal.value = false;
};

const saveStudent = async () => {
  saving.value = true;
  errors.value = {};
  try {
    if (isEditing.value) {
      await userService.update(selectedId.value, form);
      toast.success('Student profile updated successfully');
    } else {
      await userService.store(form);
      toast.success('Student created successfully');
    }
    closeModal();
    fetchStudents(pagination.current_page);
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      toast.error('Something went wrong. Please check your data.');
    }
  } finally {
    saving.value = false;
  }
};

const handleStatusToggle = async (student) => {
  if (toggling.value) return;
  
  // Optmistic UI Update
  const oldStatus = student.status;
  student.status = student.status === 'active' ? 'suspended' : 'active';
  
  try {
    await userService.toggleStatus(student.uuid);
    toast.success(`Account ${student.status === 'active' ? 'activated' : 'suspended'} successfully`);
  } catch (error) {
    student.status = oldStatus; // Rollback if error
    toast.error('Failed to update status');
  }
};

const triggerDelete = (student) => {
  studentToDelete.value = student;
  showDeleteModal.value = true;
};

const startEditing = (student) => {
  // Prevent editing admin accounts
  if (student.roles?.some(role => ['admin', 'super-admin'].includes(role))) {
    toast.error('Administrative accounts cannot be edited from Student Directory');
    return;
  }

  student.isEditing = true;
  student.editForm = {
    name: student.name,
    email: student.email,
  };
};

const saveUser = async (student) => {
  if (!student.editForm.name || !student.editForm.email) {
    toast.error('Name and email are required');
    return;
  }

  try {
    await userService.update(student.uuid, student.editForm);
    student.name = student.editForm.name;
    student.email = student.editForm.email;
    student.isEditing = false;
    delete student.editForm;
    toast.success('User updated successfully');
  } catch (error) {
    toast.error('Failed to update user');
  }
};

const cancelEditing = (student) => {
  student.isEditing = false;
  delete student.editForm;
};

const confirmDelete = async () => {
  if (!studentToDelete.value) return;
  deleting.value = true;
  try {
    await userService.destroy(studentToDelete.value.uuid);
    toast.success('Student account deleted permanently');
    showDeleteModal.value = false;
    fetchStudents(pagination.current_page);
  } catch (error) {
    toast.error('Failed to delete student');
  } finally {
    deleting.value = false;
    studentToDelete.value = null;
  }
};

onMounted(() => {
  fetchStudents();
});
</script>

<style scoped>
.panel-enter-active, .panel-leave-active {
  transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.panel-enter-from, .panel-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
