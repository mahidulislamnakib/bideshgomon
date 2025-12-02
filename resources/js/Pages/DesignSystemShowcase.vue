<template>
  <Head title="Design System Components" />
  
  <AuthenticatedLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-4xl font-bold text-gray-900">BideshGomon Design System</h1>
          <p class="mt-2 text-lg text-gray-600">Official UI components using the 3-color palette</p>
        </div>

        <!-- Color Palette -->
        <BideshCard title="Official Color Palette" class="mb-8">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <div class="h-24 bg-[#e4282b] rounded-lg mb-3 flex items-center justify-center">
                <span class="text-white font-bold text-xl">#e4282b</span>
              </div>
              <h4 class="font-semibold text-[#505050]">Primary Red</h4>
              <p class="text-sm text-gray-500">Primary actions, headers, alerts</p>
            </div>
            <div>
              <div class="h-24 bg-[#64ac44] rounded-lg mb-3 flex items-center justify-center">
                <span class="text-white font-bold text-xl">#64ac44</span>
              </div>
              <h4 class="font-semibold text-[#505050]">Primary Green</h4>
              <p class="text-sm text-gray-500">Success states, CTAs</p>
            </div>
            <div>
              <div class="h-24 bg-[#505050] rounded-lg mb-3 flex items-center justify-center">
                <span class="text-white font-bold text-xl">#505050</span>
              </div>
              <h4 class="font-semibold text-[#505050]">Primary Gray</h4>
              <p class="text-sm text-gray-500">Text, borders, secondary</p>
            </div>
          </div>
        </BideshCard>

        <!-- Buttons -->
        <BideshCard title="Buttons" class="mb-8">
          <div class="space-y-6">
            <!-- Primary Buttons -->
            <div>
              <h4 class="font-medium text-[#505050] mb-3">Solid Buttons</h4>
              <div class="flex flex-wrap gap-3">
                <BideshButton variant="primary">Primary Button</BideshButton>
                <BideshButton variant="success">Success Button</BideshButton>
                <BideshButton variant="secondary">Secondary Button</BideshButton>
                <BideshButton variant="danger">Danger Button</BideshButton>
                <BideshButton variant="primary" disabled>Disabled</BideshButton>
                <BideshButton variant="primary" loading>Loading...</BideshButton>
              </div>
            </div>

            <!-- Outline Buttons -->
            <div>
              <h4 class="font-medium text-[#505050] mb-3">Outline Buttons</h4>
              <div class="flex flex-wrap gap-3">
                <BideshButton variant="outline-primary">Outline Primary</BideshButton>
                <BideshButton variant="outline-success">Outline Success</BideshButton>
                <BideshButton variant="outline-secondary">Outline Secondary</BideshButton>
                <BideshButton variant="ghost">Ghost Button</BideshButton>
              </div>
            </div>

            <!-- Button Sizes -->
            <div>
              <h4 class="font-medium text-[#505050] mb-3">Button Sizes</h4>
              <div class="flex flex-wrap items-center gap-3">
                <BideshButton variant="primary" size="xs">Extra Small</BideshButton>
                <BideshButton variant="primary" size="sm">Small</BideshButton>
                <BideshButton variant="primary" size="md">Medium</BideshButton>
                <BideshButton variant="primary" size="lg">Large</BideshButton>
                <BideshButton variant="primary" size="xl">Extra Large</BideshButton>
              </div>
            </div>

            <!-- Buttons with Icons -->
            <div>
              <h4 class="font-medium text-[#505050] mb-3">Buttons with Icons</h4>
              <div class="flex flex-wrap gap-3">
                <BideshButton variant="primary">
                  <template #iconLeft>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </template>
                  Add New
                </BideshButton>
                <BideshButton variant="success">
                  Download
                  <template #iconRight>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                  </template>
                </BideshButton>
              </div>
            </div>
          </div>
        </BideshCard>

        <!-- Cards -->
        <BideshCard title="Cards" class="mb-8">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <BideshCard variant="default" padding="md">
              <h4 class="font-semibold text-[#505050] mb-2">Default Card</h4>
              <p class="text-sm text-gray-600">Border with shadow</p>
            </BideshCard>
            <BideshCard variant="elevated" padding="md">
              <h4 class="font-semibold text-[#505050] mb-2">Elevated Card</h4>
              <p class="text-sm text-gray-600">No border, elevated shadow</p>
            </BideshCard>
            <BideshCard variant="hoverable" padding="md" clickable @click="showAlert">
              <h4 class="font-semibold text-[#505050] mb-2">Hoverable Card</h4>
              <p class="text-sm text-gray-600">Click me for interaction</p>
            </BideshCard>
          </div>
        </BideshCard>

        <!-- Badges -->
        <BideshCard title="Badges" class="mb-8">
          <div class="space-y-6">
            <!-- Solid Badges -->
            <div>
              <h4 class="font-medium text-[#505050] mb-3">Solid Badges</h4>
              <div class="flex flex-wrap gap-2">
                <BideshBadge variant="primary">Primary</BideshBadge>
                <BideshBadge variant="success">Success</BideshBadge>
                <BideshBadge variant="warning">Warning</BideshBadge>
                <BideshBadge variant="danger">Danger</BideshBadge>
                <BideshBadge variant="info">Info</BideshBadge>
                <BideshBadge variant="default">Default</BideshBadge>
              </div>
            </div>

            <!-- Outline Badges -->
            <div>
              <h4 class="font-medium text-[#505050] mb-3">Outline Badges</h4>
              <div class="flex flex-wrap gap-2">
                <BideshBadge variant="primary" outlined>Primary</BideshBadge>
                <BideshBadge variant="success" outlined>Success</BideshBadge>
                <BideshBadge variant="warning" outlined>Warning</BideshBadge>
                <BideshBadge variant="danger" outlined>Danger</BideshBadge>
                <BideshBadge variant="info" outlined>Info</BideshBadge>
              </div>
            </div>

            <!-- Badge Sizes -->
            <div>
              <h4 class="font-medium text-[#505050] mb-3">Badge Sizes</h4>
              <div class="flex flex-wrap items-center gap-2">
                <BideshBadge variant="primary" size="sm">Small</BideshBadge>
                <BideshBadge variant="primary" size="md">Medium</BideshBadge>
                <BideshBadge variant="primary" size="lg">Large</BideshBadge>
              </div>
            </div>

            <!-- Status Badges -->
            <div>
              <h4 class="font-medium text-[#505050] mb-3">Status Badges</h4>
              <div class="flex flex-wrap gap-2">
                <BideshBadge variant="success">Active</BideshBadge>
                <BideshBadge variant="default">Inactive</BideshBadge>
                <BideshBadge variant="warning">Pending</BideshBadge>
                <BideshBadge variant="danger">Rejected</BideshBadge>
                <BideshBadge variant="primary">Approved</BideshBadge>
              </div>
            </div>
          </div>
        </BideshCard>

        <!-- Form Inputs -->
        <BideshCard title="Form Inputs" class="mb-8">
          <div class="space-y-6 max-w-2xl">
            <!-- Basic Input -->
            <BideshInput
              v-model="form.name"
              label="Full Name"
              placeholder="Enter your name"
              helper-text="This will be displayed on your profile"
              required
            />

            <!-- Input with Error -->
            <BideshInput
              v-model="form.email"
              type="email"
              label="Email Address"
              placeholder="you@example.com"
              error="This email is already taken"
              required
            />

            <!-- Input with Success -->
            <BideshInput
              v-model="form.username"
              label="Username"
              placeholder="Choose a username"
              success="This username is available!"
            />

            <!-- Input with Icons -->
            <BideshInput
              v-model="form.search"
              label="Search"
              placeholder="Search anything..."
            >
              <template #iconLeft>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </template>
            </BideshInput>

            <!-- Disabled Input -->
            <BideshInput
              v-model="form.disabled"
              label="Disabled Input"
              placeholder="Cannot edit"
              disabled
            />

            <!-- Input Sizes -->
            <div>
              <h4 class="font-medium text-[#505050] mb-3">Input Sizes</h4>
              <div class="space-y-3">
                <BideshInput v-model="form.small" size="sm" placeholder="Small input" />
                <BideshInput v-model="form.medium" size="md" placeholder="Medium input" />
                <BideshInput v-model="form.large" size="lg" placeholder="Large input" />
              </div>
            </div>
          </div>
        </BideshCard>

        <!-- Usage Example -->
        <BideshCard title="Usage Example" class="mb-8">
          <div class="prose max-w-none">
            <pre class="bg-gray-50 p-4 rounded-lg overflow-x-auto"><code>&lt;template&gt;
  &lt;BideshButton variant="primary" size="md" @click="handleClick"&gt;
    &lt;template #iconLeft&gt;
      &lt;PlusIcon class="w-5 h-5" /&gt;
    &lt;/template&gt;
    Add Item
  &lt;/BideshButton&gt;
  
  &lt;BideshCard variant="elevated" padding="lg"&gt;
    &lt;BideshInput
      v-model="form.name"
      label="Name"
      required
      error="Name is required"
    /&gt;
  &lt;/BideshCard&gt;
  
  &lt;BideshBadge variant="success"&gt;Active&lt;/BideshBadge&gt;
&lt;/template&gt;

&lt;script setup&gt;
import BideshButton from '@/Components/UI/BideshButton.vue'
import BideshCard from '@/Components/UI/BideshCard.vue'
import BideshInput from '@/Components/UI/BideshInput.vue'
import BideshBadge from '@/Components/UI/BideshBadge.vue'

const form = reactive({
  name: ''
})

const handleClick = () => {
  console.log('Button clicked!')
}
&lt;/script&gt;</code></pre>
          </div>
        </BideshCard>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BideshButton from '@/Components/UI/BideshButton.vue';
import BideshCard from '@/Components/UI/BideshCard.vue';
import BideshBadge from '@/Components/UI/BideshBadge.vue';
import BideshInput from '@/Components/UI/BideshInput.vue';

const form = reactive({
  name: '',
  email: '',
  username: 'johndoe',
  search: '',
  disabled: 'This cannot be edited',
  small: '',
  medium: '',
  large: ''
});

const showAlert = () => {
  alert('Card clicked!');
};
</script>
