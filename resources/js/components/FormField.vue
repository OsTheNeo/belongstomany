<template>
    <DefaultField 
        :field="currentField" 
        :errors="errors" 
        :show-help-text="true"
        :full-width-content="fullWidthContent"
    >
        <template #field>
            <div v-if="currentlyIsVisible" class="belongs-to-many-field relative">
                <VueMultiselect
                    ref="multiselect"
                    v-model="selectedValues"
                    :options="options"
                    :multiple="true"
                    :searchable="true"
                    :close-on-select="false"
                    :clear-on-select="false"
                    :preserve-search="true"
                    :placeholder="currentField.name"
                    :label="optionsLabel"
                    :track-by="trackBy"
                    :loading="loading"
                    :disabled="currentlyIsReadonly"
                    :max-height="300"
                    open-direction="auto"
                    @input="handleChange"
                >
                    <template #noOptions>
                        <span>{{ currentField.multiselectSlots?.noOptions || 'List is empty' }}</span>
                    </template>
                    <template #noResult>
                        <span>{{ currentField.multiselectSlots?.noResult || 'No elements found' }}</span>
                    </template>
                </VueMultiselect>
                <label v-if="currentField.selectAll" class="mt-2 flex items-center">
                    <input type="checkbox" v-model="selectAll" class="checkbox mr-2">
                    {{ currentField.messageSelectAll || 'Select All' }}
                </label>
            </div>
        </template>
    </DefaultField>
</template>

<script>
import { DependentFormField, HandlesValidationErrors } from "laravel-nova";
import VueMultiselect from 'vue-multiselect';

export default {
    mixins: [DependentFormField, HandlesValidationErrors],
    props: ["resourceName", "resourceId", "field"],
    components: { VueMultiselect },
    
    data() {
        return {
            options: [],
            selectedValues: [],
            optionsLabel: "label",
            trackBy: "id",
            loading: true,
            selectAll: false,
        };
    },
    
    mounted() {
        this.initializeComponent();
    },
    
    watch: {
        selectAll(value) {
            if (value) {
                this.selectedValues = [...this.options];
            } else {
                this.selectedValues = [];
            }
        },
        
        selectedValues: {
            handler(newVal) {
                // Emit change event for dependent fields
                this.emitFieldValueChange(this.fieldAttribute, newVal);
            },
            deep: true
        }
    },
    
    methods: {
        initializeComponent() {
            this.optionsLabel = this.currentField.optionsLabel || "label";
            this.trackBy = this.currentField.trackBy || "id";
            this.fetchOptions();
        },
        
        setInitialValue() {
            // Set initial selected values from field.value
            if (this.currentField.value && Array.isArray(this.currentField.value)) {
                this.selectedValues = this.currentField.value;
            } else {
                this.selectedValues = [];
            }
        },

        fetchOptions() {
            if (this.currentField.options) {
                this.options = this.currentField.options;
                this.loading = false;
                this.setInitialValue();
                return;
            }
            
            let baseUrl = "/nova-vendor/belongstomany/";
            let endpoint = baseUrl + this.resourceName + "/options/" + this.currentField.attribute + "/" + this.optionsLabel;
            
            // If there are dependent field values, append them
            if (this.dependsOn && Object.keys(this.watchedFields).length > 0) {
                const dependentValues = Object.entries(this.watchedFields)
                    .filter(([key, value]) => value !== null && value !== undefined)
                    .map(([key, value]) => `${key}=${encodeURIComponent(value)}`)
                    .join('&');
                    
                if (dependentValues) {
                    endpoint += '?' + dependentValues;
                }
            }
            
            Nova.request()
                .get(endpoint)
                .then((response) => {
                    this.options = response.data;
                    this.loading = false;
                    this.setInitialValue();
                })
                .catch((error) => {
                    console.error('Error fetching options:', error);
                    this.loading = false;
                });
        },
        
        onSyncedField() {
            // Called when the field is synced after a dependent field changes
            this.fetchOptions();
        },

        fill(formData) {
            this.fillIfVisible(formData, this.currentField.attribute, JSON.stringify(this.selectedValues) || "[]");
        },
        
        handleChange(value) {
            this.selectedValues = value;
        },
    },
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<style>
.belongs-to-many-field {
    position: relative;
    z-index: 50;
}

.belongs-to-many-field .multiselect--active {
    z-index: 9999;
}

.belongs-to-many-field .multiselect__content-wrapper {
    position: absolute;
    z-index: 9999 !important;
    width: 100%;
    max-height: 300px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #d1d5db;
    border-top: none;
    border-radius: 0 0 0.5rem 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.multiselect__placeholder {
    font-size: 0.875rem;
    color: #9ca3af !important;
    padding-top: 2px;
}

.multiselect__tags {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    min-height: 36px;
    padding: 6px 40px 0 8px;
}

.multiselect__tag {
    background: #8b5cf6;
    border-radius: 0.25rem;
}

.multiselect__tag-icon:after {
    color: white;
}

.multiselect__tag-icon:hover {
    background: #7c3aed;
}

.multiselect__option--highlight {
    background: #8b5cf6;
}

.multiselect__spinner {
    background: #fff;
    border-radius: 0.5rem;
}

/* Dark mode */
.dark .multiselect__tags {
    background: #1f2937;
    border-color: #374151;
}

.dark .multiselect__content-wrapper {
    background: #1f2937;
    border-color: #374151;
}

.dark .multiselect__option {
    background: #1f2937;
    color: #d1d5db;
}

.dark .multiselect__option--highlight {
    background: #7c3aed;
}

.dark .multiselect__input,
.dark .multiselect__single {
    background: transparent;
    color: #d1d5db;
}

.dark .multiselect__spinner {
    background: #1f2937;
}
</style>
