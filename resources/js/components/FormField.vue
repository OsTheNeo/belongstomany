<template>
    <DefaultField :field="field" :errors="errors" :show-help-text="true">
        <template #field>
            <div class="belongs-to-many-field relative">
                <VueMultiselect
                    ref="multiselect"
                    v-model="selectedValues"
                    :options="options"
                    :multiple="true"
                    :searchable="true"
                    :close-on-select="false"
                    :clear-on-select="false"
                    :preserve-search="true"
                    :placeholder="field.name"
                    :label="optionsLabel"
                    :track-by="trackBy"
                    :loading="loading"
                    :disabled="field.readonly"
                    :max-height="300"
                    open-direction="auto"
                >
                    <template #noOptions>
                        <span>{{ field.multiselectSlots?.noOptions || 'List is empty' }}</span>
                    </template>
                    <template #noResult>
                        <span>{{ field.multiselectSlots?.noResult || 'No elements found' }}</span>
                    </template>
                </VueMultiselect>
                <label v-if="field.selectAll" class="mt-2 flex items-center">
                    <input type="checkbox" v-model="selectAll" class="checkbox mr-2">
                    {{ field.messageSelectAll || 'Select All' }}
                </label>
            </div>
        </template>
    </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from "laravel-nova";
import VueMultiselect from 'vue-multiselect';

export default {
    mixins: [FormField, HandlesValidationErrors],
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
        this.field.visible = true;
    },
    watch: {
        selectAll(value) {
            if (value) {
                this.selectedValues = [...this.options];
            } else {
                this.selectedValues = [];
            }
        },
    },
    methods: {
        setInitialValue() {
            this.optionsLabel = this.field.optionsLabel || "label";
            this.trackBy = this.field.trackBy || "id";
            
            if (this.field.value && Array.isArray(this.field.value)) {
                this.selectedValues = this.field.value;
            } else {
                this.selectedValues = [];
            }
            
            this.fetchOptions();
        },

        fetchOptions() {
            if (this.field.options) {
                this.options = this.field.options;
                this.loading = false;
                return;
            }
            
            let baseUrl = "/nova-vendor/belongstomany/";
            
            Nova.request()
                .get(baseUrl + this.resourceName + "/options/" + this.field.attribute + "/" + this.optionsLabel)
                .then((response) => {
                    this.options = response.data;
                    this.loading = false;
                })
                .catch((error) => {
                    console.error('Error fetching options:', error);
                    this.loading = false;
                });
        },

        fill(formData) {
            formData.append(this.field.attribute, JSON.stringify(this.selectedValues) || "[]");
        },
    },
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<style>
/* Fix dropdown being cut off by parent overflow */
.belongs-to-many-field {
    position: relative;
    z-index: 50;
}

.belongs-to-many-field .multiselect {
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
    border: 1px solid rgba(var(--colors-gray-300), 1);
    border-top: none;
    border-radius: 0 0 0.5rem 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.multiselect__placeholder {
    font-size: 0.875rem;
    color: rgba(var(--colors-gray-400), 1) !important;
    padding-top: 2px;
}

.multiselect__tags {
    border: 1px solid rgba(var(--colors-gray-300), 1);
    border-radius: 0.5rem;
    min-height: 36px;
    padding: 6px 40px 0 8px;
}

.multiselect__tag {
    background: rgba(var(--colors-primary-500), 1);
    border-radius: 0.25rem;
}

.multiselect__tag-icon:after {
    color: white;
}

.multiselect__tag-icon:hover {
    background: rgba(var(--colors-primary-600), 1);
}

.multiselect__option--highlight {
    background: rgba(var(--colors-primary-500), 1);
}

.multiselect__spinner {
    background: #fff;
    border-radius: 0.5rem;
}

/* Dark mode support */
.dark .multiselect__content-wrapper {
    background: rgba(var(--colors-gray-900), 1);
    border-color: rgba(var(--colors-gray-700), 1);
}

.dark .multiselect__tags {
    background: rgba(var(--colors-gray-900), 1);
    border-color: rgba(var(--colors-gray-700), 1);
}

.dark .multiselect__input,
.dark .multiselect__single {
    background: transparent;
    color: rgba(var(--colors-gray-300), 1);
}

.dark .multiselect__option {
    background: rgba(var(--colors-gray-900), 1);
    color: rgba(var(--colors-gray-300), 1);
}

.dark .multiselect__option--highlight {
    background: rgba(var(--colors-primary-600), 1);
    color: white;
}
</style>
