<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
    role="dialog"
    aria-modal="true"
    aria-label="New Message"
    @click.self="handleClose"
    @keydown.esc="handleClose"
  >
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
      <!-- Modal Header -->
      <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-full bg-[#062f78] text-white flex items-center justify-center font-bold text-sm shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
          </div>
          <div>
            <h3 class="text-base font-semibold text-slate-800 leading-tight">New Message</h3>
            <p class="text-xs text-slate-500 leading-tight mt-0.5">Start a conversation with clergy, commissions, staff, or parishioners</p>
          </div>
        </div>
        <button
          type="button"
          @click="handleClose"
          class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 transition cursor-pointer"
          aria-label="Close"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Form Body -->
      <form @submit.prevent="handleSubmit" class="p-5 overflow-y-auto space-y-4">
        <!-- 1. Recipient Category Dropdown -->
        <div>
          <label for="recipient-type" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            Recipient Category
          </label>
          <div class="relative">
            <select
              id="recipient-type"
              v-model="selectedCategory"
              @change="onCategoryChange"
              class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:border-[#062f78] focus:bg-white focus:ring-2 focus:ring-[#062f78]/15 transition font-medium text-slate-800 cursor-pointer"
            >
              <option value="all">All Contacts &amp; Groups</option>
              <option value="parishioner">Parishioners</option>
              <option value="administrator">Parish Administrators &amp; Secretariat</option>
              <option value="staff">Staff &amp; Commission Coordinators</option>
              <option value="parish_priest">Parish Priest</option>
              <option value="parochial_vicar">Parochial Vicar</option>
              <option value="commission">Commissions</option>
              <option value="ministry">Ministries</option>
            </select>
          </div>
        </div>

        <!-- 2. Specific Recipient Picker -->
        <div v-if="selectedCategory">
          <label for="recipient-select" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            Select Recipient
          </label>

          <!-- Search filter within category -->
          <div class="relative mb-2">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </span>
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="selectedCategory === 'all' ? 'Search all users, staff, clergy, commissions...' : `Search in ${categoryLabel}...`"
              class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-hidden focus:border-[#062f78] focus:bg-white focus:ring-2 focus:ring-[#062f78]/15 transition placeholder:text-slate-400"
            />
          </div>

          <!-- Loading state -->
          <div v-if="isLoading" class="py-8 text-center text-slate-400 text-xs font-medium">
            <div class="inline-block w-5 h-5 border-2 border-[#062f78] border-t-transparent rounded-full animate-spin mb-1"></div>
            <p>Loading directory...</p>
          </div>

          <!-- Filtered items list (Radio / clickable row select) -->
          <div v-else class="max-h-48 overflow-y-auto rounded-xl border border-slate-200 divide-y divide-slate-100 bg-white shadow-2xs">
            <div
              v-for="item in categoryFilteredItems"
              :key="item.item_id"
              @click="selectedRecipient = item"
              class="px-3 py-2.5 flex items-center justify-between hover:bg-slate-50 cursor-pointer transition select-none"
              :class="selectedRecipient?.item_id === item.item_id ? 'bg-blue-50/80' : ''"
            >
              <div class="flex items-center gap-2.5 min-w-0">
                <div
                  class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0 overflow-hidden"
                  :class="item.type === 'commission' ? 'bg-amber-100 text-amber-900 border border-amber-300' : (item.type === 'ministry' ? 'bg-purple-100 text-purple-900' : 'bg-[#062f78] text-white')"
                >
                  <span v-if="item.type === 'commission'">🏛️</span>
                  <span v-else-if="item.type === 'ministry'">👥</span>
                  <img
                    v-else-if="item.avatar"
                    :src="item.avatar"
                    :alt="item.name"
                    class="w-full h-full object-cover rounded-full"
                    referrerpolicy="no-referrer"
                    @error="e => e.target.style.display = 'none'"
                  />
                  <span v-else>{{ getInitials(item.name) }}</span>
                </div>
                <div class="min-w-0">
                  <p class="text-xs font-semibold text-slate-800 truncate" :class="selectedRecipient?.item_id === item.item_id ? 'text-[#062f78]' : ''">
                    {{ item.name }}
                  </p>
                  <p class="text-[11px] text-slate-500 truncate leading-tight">
                    {{ item.subtitle }}
                  </p>
                </div>
              </div>
              <div class="flex items-center ml-2">
                <span
                  class="w-4 h-4 rounded-full border flex items-center justify-center transition"
                  :class="selectedRecipient?.item_id === item.item_id ? 'border-[#062f78] bg-[#062f78]' : 'border-slate-300'"
                >
                  <svg v-if="selectedRecipient?.item_id === item.item_id" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                  </svg>
                </span>
              </div>
            </div>

            <div v-if="categoryFilteredItems.length === 0" class="py-6 text-center text-slate-400 text-xs">
              No contacts found matching your search.
            </div>
          </div>
        </div>

        <!-- 3. Message Textarea -->
        <div v-if="selectedRecipient">
          <label for="new-message-body" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            Message
          </label>
          <textarea
            id="new-message-body"
            v-model="messageBody"
            rows="3"
            maxlength="5000"
            placeholder="Type your message... (Optional, or click Send to start chat)"
            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:border-[#062f78] focus:bg-white focus:ring-2 focus:ring-[#062f78]/15 transition resize-none placeholder:text-slate-400"
          ></textarea>
        </div>

        <!-- Modal Actions -->
        <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
          <button
            type="button"
            @click="handleClose"
            class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="!selectedRecipient || isSubmitting"
            class="px-4 py-2 text-xs font-semibold rounded-lg text-white transition shadow-xs cursor-pointer flex items-center gap-1.5"
            :class="selectedRecipient && !isSubmitting ? 'bg-[#062f78] hover:bg-[#0d4399] active:scale-95' : 'bg-slate-300 cursor-not-allowed'"
          >
            <svg v-if="isSubmitting" class="w-3.5 h-3.5 animate-spin text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ messageBody.trim() ? 'Send Message' : 'Open Conversation' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'select-user', 'select-commission', 'select-ministry', 'send-new-message']);

const selectedCategory = ref('all');
const selectedRecipient = ref(null);
const searchQuery = ref('');
const messageBody = ref('');
const isLoading = ref(false);
const isSubmitting = ref(false);

const users = ref([]);
const commissions = ref([]);
const ministries = ref([]);

let searchDebounceTimer = null;

const fetchDirectory = async (search = '') => {
  isLoading.value = true;
  try {
    const params = search ? { q: search } : {};
    const res = await axios.get('/api/messages/directory', { params });
    users.value = res.data.users || (res.data.categories?.all ? res.data.categories.all.filter((x) => x.type === 'user') : []);
    commissions.value = res.data.commissions || res.data.categories?.commission || [];
    ministries.value = res.data.ministries || res.data.categories?.ministry || [];
  } catch (error) {
    console.error('Failed to load message directory:', error);
  } finally {
    isLoading.value = false;
  }
};

watch(searchQuery, (newVal) => {
  clearTimeout(searchDebounceTimer);
  const trimmed = newVal.trim();
  searchDebounceTimer = setTimeout(() => {
    fetchDirectory(trimmed);
  }, 250);
});

watch(
  () => props.isOpen,
  (val) => {
    if (val) {
      if (users.value.length === 0) {
        fetchDirectory();
      }
      resetForm();
    }
  }
);

onMounted(() => {
  if (props.isOpen) {
    fetchDirectory();
  }
});

const categoryLabel = computed(() => {
  switch (selectedCategory.value) {
    case 'all': return 'All Contacts & Groups';
    case 'parishioner': return 'Parishioners';
    case 'commission': return 'Commissions';
    case 'ministry': return 'Ministries';
    case 'staff': return 'Staff';
    case 'parish_priest': return 'Parish Priest';
    case 'parochial_vicar': return 'Parochial Vicar';
    case 'administrator': return 'Parish Administrators & Secretariat';
    default: return 'Recipients';
  }
});

const onCategoryChange = () => {
  selectedRecipient.value = null;
};

const categoryFilteredItems = computed(() => {
  if (!selectedCategory.value) return [];
  const q = searchQuery.value.trim().toLowerCase();

  const formatRoleLabel = (u) => {
    return u.role_label || u.role_badge_label || u.role || 'Member';
  };

  const mapUser = (u) => ({
    item_id: `user-${u.id}`,
    type: 'user',
    id: u.id,
    name: u.display_name || u.name,
    email: u.email || '',
    subtitle: u.subtitle || formatRoleLabel(u),
    avatar: u.avatar_url || u.profile_photo_url || u.avatar || null,
    data: u,
    role: u.role,
  });

  const mapComm = (c) => ({
    item_id: `comm-${c.id}`,
    type: 'commission',
    id: c.id,
    name: c.name,
    email: '',
    subtitle: c.subtitle || `${c.members_count || 0} active members · Commission Channel`,
    avatar: null,
    data: c,
    role: 'commission',
  });

  const mapMin = (m) => ({
    item_id: `min-${m.id}`,
    type: 'ministry',
    id: m.id,
    name: m.name,
    email: '',
    subtitle: m.subtitle || 'Ministry Group',
    avatar: null,
    data: m,
    role: 'ministry',
  });

  let items = [];

  if (selectedCategory.value === 'all') {
    items = [
      ...users.value.map(mapUser),
      ...commissions.value.map(mapComm),
      ...ministries.value.map(mapMin),
    ];
  } else if (selectedCategory.value === 'administrator') {
    items = users.value
      .filter((u) => {
        const role = (u.role || '').toLowerCase();
        const roleLabel = (u.role_label || u.role_badge_label || '').toLowerCase();
        return (
          ['super_admin', 'admin', 'parish_secretary', 'parish_priest', 'parochial_vicar'].includes(role) ||
          roleLabel.includes('admin') ||
          roleLabel.includes('secretary')
        );
      })
      .map(mapUser);
  } else if (selectedCategory.value === 'parish_priest') {
    items = users.value
      .filter((u) => u.role === 'parish_priest' || (u.role_label || '').toLowerCase().includes('priest'))
      .map(mapUser);
  } else if (selectedCategory.value === 'parochial_vicar') {
    items = users.value
      .filter((u) => u.role === 'parochial_vicar' || (u.role_label || '').toLowerCase().includes('vicar'))
      .map(mapUser);
  } else if (selectedCategory.value === 'commission') {
    items = commissions.value.map(mapComm);
  } else if (selectedCategory.value === 'ministry') {
    items = ministries.value.map(mapMin);
  } else if (selectedCategory.value === 'staff') {
    items = users.value
      .filter((u) => {
        const role = (u.role || '').toLowerCase();
        const roleLabel = (u.role_label || u.role_badge_label || '').toLowerCase();
        return (
          ['staff', 'commission_admin', 'commission_member', 'parish_secretary'].includes(role) ||
          roleLabel.includes('staff') ||
          roleLabel.includes('secretary') ||
          roleLabel.includes('coordinator')
        );
      })
      .map(mapUser);
  } else if (selectedCategory.value === 'parishioner') {
    items = users.value
      .filter((u) => ['parishioner', 'user'].includes(u.role) || !u.role || u.role === 'member')
      .map(mapUser);
  } else {
    items = users.value.map(mapUser);
  }

  if (q) {
    items = items.filter((item) =>
      (item.name || '').toLowerCase().includes(q) ||
      (item.subtitle || '').toLowerCase().includes(q) ||
      (item.email || '').toLowerCase().includes(q)
    );
  }

  return items;
});

const getInitials = (name) => {
  if (!name) return '?';
  return name
    .split(' ')
    .map((w) => w[0])
    .filter(Boolean)
    .slice(0, 2)
    .join('')
    .toUpperCase();
};

const resetForm = () => {
  selectedCategory.value = 'all';
  selectedRecipient.value = null;
  searchQuery.value = '';
  messageBody.value = '';
  isSubmitting.value = false;
};

const handleClose = () => {
  resetForm();
  emit('close');
};

const handleSubmit = () => {
  if (!selectedRecipient.value) return;

  const recipient = selectedRecipient.value;
  const body = messageBody.value.trim();

  if (body) {
    emit('send-new-message', {
      type: recipient.type,
      data: recipient.data,
      body,
    });
  } else {
    if (recipient.type === 'commission') {
      emit('select-commission', recipient.data);
    } else if (recipient.type === 'ministry') {
      emit('select-ministry', recipient.data);
    } else {
      emit('select-user', recipient.data);
    }
  }

  handleClose();
};
</script>
