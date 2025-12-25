<template>
  <div :class="['sql-query-builder', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
          <CircleStackIcon class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">SQL Query Builder</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">Build queries visually</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          @click="clearQuery"
        >
          Clear
        </button>
        <button
          type="button"
          class="px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors flex items-center gap-2"
          @click="executeQuery"
        >
          <PlayIcon class="w-4 h-4" />
          Run Query
        </button>
      </div>
    </div>

    <div class="p-4 space-y-4">
      <!-- Operation Type -->
      <div class="flex items-center gap-4">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-24">Operation:</label>
        <div class="flex gap-2">
          <button
            v-for="op in operations"
            :key="op.value"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
              operation === op.value
                ? 'bg-indigo-600 text-white'
                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
            ]"
            @click="operation = op.value"
          >
            {{ op.label }}
          </button>
        </div>
      </div>

      <!-- Table Selection -->
      <div class="flex items-center gap-4">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-24">Table:</label>
        <select
          v-model="selectedTable"
          class="flex-1 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
        >
          <option value="">Select a table...</option>
          <option v-for="table in tables" :key="table.name" :value="table.name">
            {{ table.name }}
          </option>
        </select>
      </div>

      <!-- Columns Selection (for SELECT) -->
      <div v-if="operation === 'SELECT'" class="flex items-start gap-4">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-24 pt-2">Columns:</label>
        <div class="flex-1">
          <div class="flex flex-wrap gap-2 mb-2">
            <button
              :class="[
                'px-3 py-1.5 text-sm rounded-lg transition-colors',
                selectedColumns.length === 0
                  ? 'bg-indigo-600 text-white'
                  : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
              ]"
              @click="selectedColumns = []"
            >
              * (All)
            </button>
            <button
              v-for="col in availableColumns"
              :key="col.name"
              :class="[
                'px-3 py-1.5 text-sm rounded-lg transition-colors',
                selectedColumns.includes(col.name)
                  ? 'bg-indigo-600 text-white'
                  : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
              ]"
              @click="toggleColumn(col.name)"
            >
              {{ col.name }}
              <span class="text-xs opacity-70 ml-1">({{ col.type }})</span>
            </button>
          </div>
        </div>
      </div>

      <!-- WHERE Conditions -->
      <div class="flex items-start gap-4">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-24 pt-2">Where:</label>
        <div class="flex-1 space-y-2">
          <div
            v-for="(condition, index) in conditions"
            :key="index"
            class="flex items-center gap-2"
          >
            <select
              v-if="index > 0"
              v-model="condition.logic"
              class="w-20 px-2 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
            >
              <option value="AND">AND</option>
              <option value="OR">OR</option>
            </select>
            <span v-else class="w-20" />
            
            <select
              v-model="condition.column"
              class="flex-1 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
            >
              <option value="">Column...</option>
              <option v-for="col in availableColumns" :key="col.name" :value="col.name">
                {{ col.name }}
              </option>
            </select>
            
            <select
              v-model="condition.operator"
              class="w-28 px-2 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
            >
              <option v-for="op in operators" :key="op.value" :value="op.value">
                {{ op.label }}
              </option>
            </select>
            
            <input
              v-model="condition.value"
              type="text"
              placeholder="Value..."
              class="flex-1 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
            />
            
            <button
              type="button"
              class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
              @click="removeCondition(index)"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
          
          <button
            type="button"
            class="flex items-center gap-2 px-3 py-2 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors"
            @click="addCondition"
          >
            <PlusIcon class="w-4 h-4" />
            Add Condition
          </button>
        </div>
      </div>

      <!-- ORDER BY (for SELECT) -->
      <div v-if="operation === 'SELECT'" class="flex items-start gap-4">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-24 pt-2">Order By:</label>
        <div class="flex-1 space-y-2">
          <div
            v-for="(order, index) in orderBy"
            :key="index"
            class="flex items-center gap-2"
          >
            <select
              v-model="order.column"
              class="flex-1 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
            >
              <option value="">Column...</option>
              <option v-for="col in availableColumns" :key="col.name" :value="col.name">
                {{ col.name }}
              </option>
            </select>
            
            <select
              v-model="order.direction"
              class="w-28 px-2 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
            >
              <option value="ASC">ASC</option>
              <option value="DESC">DESC</option>
            </select>
            
            <button
              type="button"
              class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
              @click="removeOrderBy(index)"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
          
          <button
            type="button"
            class="flex items-center gap-2 px-3 py-2 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors"
            @click="addOrderBy"
          >
            <PlusIcon class="w-4 h-4" />
            Add Order
          </button>
        </div>
      </div>

      <!-- LIMIT (for SELECT) -->
      <div v-if="operation === 'SELECT'" class="flex items-center gap-4">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-24">Limit:</label>
        <input
          v-model.number="limit"
          type="number"
          min="0"
          placeholder="No limit"
          class="w-32 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
        />
        <input
          v-if="limit"
          v-model.number="offset"
          type="number"
          min="0"
          placeholder="Offset"
          class="w-32 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
        />
      </div>

      <!-- SET Values (for UPDATE) -->
      <div v-if="operation === 'UPDATE'" class="flex items-start gap-4">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-24 pt-2">Set:</label>
        <div class="flex-1 space-y-2">
          <div
            v-for="(set, index) in setValues"
            :key="index"
            class="flex items-center gap-2"
          >
            <select
              v-model="set.column"
              class="flex-1 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
            >
              <option value="">Column...</option>
              <option v-for="col in availableColumns" :key="col.name" :value="col.name">
                {{ col.name }}
              </option>
            </select>
            
            <span class="text-gray-500">=</span>
            
            <input
              v-model="set.value"
              type="text"
              placeholder="Value..."
              class="flex-1 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
            />
            
            <button
              type="button"
              class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
              @click="removeSetValue(index)"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
          
          <button
            type="button"
            class="flex items-center gap-2 px-3 py-2 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors"
            @click="addSetValue"
          >
            <PlusIcon class="w-4 h-4" />
            Add Value
          </button>
        </div>
      </div>

      <!-- INSERT Values -->
      <div v-if="operation === 'INSERT'" class="flex items-start gap-4">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 w-24 pt-2">Values:</label>
        <div class="flex-1 space-y-2">
          <div
            v-for="(row, rowIndex) in insertValues"
            :key="rowIndex"
            class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg"
          >
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Row {{ rowIndex + 1 }}</span>
              <button
                type="button"
                class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                @click="removeInsertRow(rowIndex)"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div v-for="col in availableColumns" :key="col.name" class="flex flex-col gap-1">
                <label class="text-xs text-gray-500">{{ col.name }}</label>
                <input
                  v-model="row[col.name]"
                  type="text"
                  :placeholder="col.type"
                  class="px-2 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-sm"
                />
              </div>
            </div>
          </div>
          
          <button
            type="button"
            class="flex items-center gap-2 px-3 py-2 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors"
            @click="addInsertRow"
          >
            <PlusIcon class="w-4 h-4" />
            Add Row
          </button>
        </div>
      </div>
    </div>

    <!-- Generated SQL -->
    <div class="border-t border-gray-200 dark:border-gray-700 p-4">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Generated SQL:</span>
        <button
          type="button"
          class="flex items-center gap-1 px-2 py-1 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
          @click="copyQuery"
        >
          <ClipboardDocumentIcon class="w-4 h-4" />
          {{ copied ? 'Copied!' : 'Copy' }}
        </button>
      </div>
      <pre class="p-4 bg-gray-900 text-green-400 rounded-lg text-sm font-mono overflow-x-auto whitespace-pre-wrap">{{ generatedQuery }}</pre>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
  CircleStackIcon,
  PlayIcon,
  PlusIcon,
  TrashIcon,
  ClipboardDocumentIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  tables: {
    type: Array,
    default: () => [
      {
        name: 'users',
        columns: [
          { name: 'id', type: 'INT' },
          { name: 'name', type: 'VARCHAR' },
          { name: 'email', type: 'VARCHAR' },
          { name: 'created_at', type: 'TIMESTAMP' }
        ]
      },
      {
        name: 'orders',
        columns: [
          { name: 'id', type: 'INT' },
          { name: 'user_id', type: 'INT' },
          { name: 'total', type: 'DECIMAL' },
          { name: 'status', type: 'VARCHAR' }
        ]
      }
    ]
  },
  theme: {
    type: String,
    default: 'light',
    validator: (v) => ['light', 'dark'].includes(v)
  },
  showHeader: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['execute', 'change'])

const operation = ref('SELECT')
const selectedTable = ref('')
const selectedColumns = ref([])
const conditions = ref([])
const orderBy = ref([])
const limit = ref(null)
const offset = ref(0)
const setValues = ref([])
const insertValues = ref([])
const copied = ref(false)

const operations = [
  { value: 'SELECT', label: 'SELECT' },
  { value: 'INSERT', label: 'INSERT' },
  { value: 'UPDATE', label: 'UPDATE' },
  { value: 'DELETE', label: 'DELETE' }
]

const operators = [
  { value: '=', label: '=' },
  { value: '!=', label: '!=' },
  { value: '>', label: '>' },
  { value: '<', label: '<' },
  { value: '>=', label: '>=' },
  { value: '<=', label: '<=' },
  { value: 'LIKE', label: 'LIKE' },
  { value: 'IN', label: 'IN' },
  { value: 'IS NULL', label: 'IS NULL' },
  { value: 'IS NOT NULL', label: 'IS NOT NULL' }
]

const themeClasses = computed(() => [
  'rounded-xl border overflow-hidden',
  props.theme === 'dark'
    ? 'bg-gray-800 border-gray-700'
    : 'bg-white border-gray-200'
])

const availableColumns = computed(() => {
  const table = props.tables.find(t => t.name === selectedTable.value)
  return table?.columns || []
})

const generatedQuery = computed(() => {
  if (!selectedTable.value) return '-- Select a table to build query'
  
  let query = ''
  
  switch (operation.value) {
    case 'SELECT':
      query = buildSelectQuery()
      break
    case 'INSERT':
      query = buildInsertQuery()
      break
    case 'UPDATE':
      query = buildUpdateQuery()
      break
    case 'DELETE':
      query = buildDeleteQuery()
      break
  }
  
  return query
})

const buildSelectQuery = () => {
  const cols = selectedColumns.value.length > 0 
    ? selectedColumns.value.join(', ')
    : '*'
  
  let query = `SELECT ${cols}\nFROM ${selectedTable.value}`
  
  const whereClause = buildWhereClause()
  if (whereClause) {
    query += `\n${whereClause}`
  }
  
  if (orderBy.value.length > 0) {
    const orders = orderBy.value
      .filter(o => o.column)
      .map(o => `${o.column} ${o.direction}`)
      .join(', ')
    if (orders) {
      query += `\nORDER BY ${orders}`
    }
  }
  
  if (limit.value) {
    query += `\nLIMIT ${limit.value}`
    if (offset.value > 0) {
      query += ` OFFSET ${offset.value}`
    }
  }
  
  return query + ';'
}

const buildInsertQuery = () => {
  if (insertValues.value.length === 0 || availableColumns.value.length === 0) {
    return `INSERT INTO ${selectedTable.value}\n-- Add values to insert`
  }
  
  const cols = availableColumns.value.map(c => c.name).join(', ')
  const values = insertValues.value.map(row => {
    const vals = availableColumns.value.map(c => {
      const val = row[c.name]
      return val ? `'${val}'` : 'NULL'
    }).join(', ')
    return `(${vals})`
  }).join(',\n  ')
  
  return `INSERT INTO ${selectedTable.value} (${cols})\nVALUES\n  ${values};`
}

const buildUpdateQuery = () => {
  const sets = setValues.value
    .filter(s => s.column && s.value !== undefined)
    .map(s => `${s.column} = '${s.value}'`)
    .join(',\n  ')
  
  if (!sets) {
    return `UPDATE ${selectedTable.value}\n-- Add SET values`
  }
  
  let query = `UPDATE ${selectedTable.value}\nSET\n  ${sets}`
  
  const whereClause = buildWhereClause()
  if (whereClause) {
    query += `\n${whereClause}`
  }
  
  return query + ';'
}

const buildDeleteQuery = () => {
  let query = `DELETE FROM ${selectedTable.value}`
  
  const whereClause = buildWhereClause()
  if (whereClause) {
    query += `\n${whereClause}`
  } else {
    query += '\n-- WARNING: No WHERE clause - will delete all rows!'
  }
  
  return query + ';'
}

const buildWhereClause = () => {
  const validConditions = conditions.value.filter(c => c.column && c.operator)
  
  if (validConditions.length === 0) return ''
  
  const clauses = validConditions.map((c, i) => {
    let clause = ''
    if (i > 0) {
      clause = `${c.logic} `
    }
    
    if (c.operator === 'IS NULL' || c.operator === 'IS NOT NULL') {
      clause += `${c.column} ${c.operator}`
    } else if (c.operator === 'IN') {
      clause += `${c.column} IN (${c.value})`
    } else if (c.operator === 'LIKE') {
      clause += `${c.column} LIKE '${c.value}'`
    } else {
      clause += `${c.column} ${c.operator} '${c.value}'`
    }
    
    return clause
  })
  
  return `WHERE ${clauses.join('\n  ')}`
}

const toggleColumn = (colName) => {
  const index = selectedColumns.value.indexOf(colName)
  if (index === -1) {
    selectedColumns.value.push(colName)
  } else {
    selectedColumns.value.splice(index, 1)
  }
}

const addCondition = () => {
  conditions.value.push({
    logic: 'AND',
    column: '',
    operator: '=',
    value: ''
  })
}

const removeCondition = (index) => {
  conditions.value.splice(index, 1)
}

const addOrderBy = () => {
  orderBy.value.push({
    column: '',
    direction: 'ASC'
  })
}

const removeOrderBy = (index) => {
  orderBy.value.splice(index, 1)
}

const addSetValue = () => {
  setValues.value.push({
    column: '',
    value: ''
  })
}

const removeSetValue = (index) => {
  setValues.value.splice(index, 1)
}

const addInsertRow = () => {
  const row = {}
  availableColumns.value.forEach(col => {
    row[col.name] = ''
  })
  insertValues.value.push(row)
}

const removeInsertRow = (index) => {
  insertValues.value.splice(index, 1)
}

const clearQuery = () => {
  selectedColumns.value = []
  conditions.value = []
  orderBy.value = []
  limit.value = null
  offset.value = 0
  setValues.value = []
  insertValues.value = []
}

const copyQuery = async () => {
  try {
    await navigator.clipboard.writeText(generatedQuery.value)
    copied.value = true
    setTimeout(() => copied.value = false, 2000)
  } catch (err) {
    console.error('Failed to copy:', err)
  }
}

const executeQuery = () => {
  emit('execute', generatedQuery.value)
}

watch(generatedQuery, (query) => {
  emit('change', query)
})

watch(selectedTable, () => {
  selectedColumns.value = []
  conditions.value = []
  orderBy.value = []
  setValues.value = []
  insertValues.value = []
})
</script>
