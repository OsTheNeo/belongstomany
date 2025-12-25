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
                    :internal-search="!isServerSearchable"
                    open-direction="auto"
                    @search-change="handleSearchChange"
                >
                    <template #noOptions>
                        <span v-if="isServerSearchable && !searchQuery">Type to search...</span>
                        <span v-else>{{ currentField.multiselectSlots?.noOptions || 'List is empty' }}</span>
                    </template>
                    <template #noResult>
                        <span>{{ currentField.multiselectSlots?.noResult || 'No elements found' }}</span>
                    </template>
                </VueMultiselect>
                <label v-if="currentField.selectAll && !isServerSearchable" class="mt-2 flex items-center">
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
            searchQuery: '',
            searchTimeout: null,
        };
    },

    computed: {
        isServerSearchable() {
            return this.currentField.searchable === true;
        }
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
            if (this.currentField.value && Array.isArray(this.currentField.value)) {
                this.selectedValues = this.currentField.value;
            } else {
                this.selectedValues = [];
            }
        },

        buildEndpoint(search = null) {
            let baseUrl = "/nova-vendor/belongstomany/";
            let endpoint = baseUrl + this.resourceName + "/options/" + this.currentField.attribute + "/" + this.optionsLabel;

            const params = new URLSearchParams();

            if (this.dependsOn && Object.keys(this.watchedFields).length > 0) {
                Object.entries(this.watchedFields)
                    .filter(([key, value]) => value !== null && value !== undefined)
                    .forEach(([key, value]) => params.append(key, value));
            }

            if (search) {
                params.append('search', search);
            }

            if (this.isServerSearchable) {
                params.append('limit', this.currentField.optionsLimit || 1000);
            }

            const queryString = params.toString();
            return queryString ? endpoint + '?' + queryString : endpoint;
        },

        fetchOptions(search = null) {
            if (this.currentField.options) {
                this.options = this.currentField.options;
                this.loading = false;
                this.setInitialValue();
                return;
            }

            this.loading = true;
            const endpoint = this.buildEndpoint(search);

            Nova.request()
                .get(endpoint)
                .then((response) => {
                    const searchResults = response.data;

                    if (search && this.selectedValues.length > 0) {
                        const selectedNotInResults = this.selectedValues.filter(
                            selected => !searchResults.some(result => result[this.trackBy] === selected[this.trackBy])
                        );
                        this.options = [...selectedNotInResults, ...searchResults];
                    } else {
                        this.options = searchResults;
                    }

                    this.loading = false;

                    if (!search) {
                        this.setInitialValue();
                    }
                })
                .catch((error) => {
                    console.error('Error fetching options:', error);
                    this.loading = false;
                });
        },

        handleSearchChange(query) {
            this.searchQuery = query;

            if (!this.isServerSearchable) {
                return;
            }

            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            this.searchTimeout = setTimeout(() => {
                this.fetchOptions(query);
            }, 300);
        },

        onSyncedField() {
            this.fetchOptions();
        },

        fill(formData) {
            this.fillIfVisible(formData, this.currentField.attribute, JSON.stringify(this.selectedValues) || "[]");
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
    background: rgba(var(--colors-white), 1);
    border: 1px solid rgba(var(--colors-gray-300), 1);
    border-top: none;
    border-radius: 0 0 0.5rem 0.5rem;
    box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
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
    background: rgba(var(--colors-white), 1);
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
    background: rgba(var(--colors-white), 1);
    border-radius: 0.5rem;
}

/* Dark mode */
.dark .multiselect__tags {
    background: rgba(var(--colors-gray-900), 1);
    border-color: rgba(var(--colors-gray-700), 1);
}

.dark .multiselect__content-wrapper {
    background: rgba(var(--colors-gray-900), 1);
    border-color: rgba(var(--colors-gray-700), 1);
}

.dark .multiselect__option {
    background: rgba(var(--colors-gray-900), 1);
    color: rgba(var(--colors-gray-300), 1);
}

.dark .multiselect__option--highlight {
    background: rgba(var(--colors-primary-600), 1);
}

.dark .multiselect__input,
.dark .multiselect__single {
    background: transparent;
    color: rgba(var(--colors-gray-300), 1);
}

.dark .multiselect__spinner {
    background: rgba(var(--colors-gray-900), 1);
}
</style>
