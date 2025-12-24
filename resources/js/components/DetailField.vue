<template>
    <PanelItem :field="field">
        <template #value>
            <template v-if="hasValues">
                <span v-for="(item, key) in values" :key="item.id" class="single">
                    <Link 
                        v-if="field.viewable" 
                        :href="$url(`/resources/${field.resourceNameRelationship}/${item.id}`)" 
                        class="link-default no-underline font-bold dim mr-1"
                    >
                        {{ getLabel(item) }}
                    </Link>
                    <span v-else>{{ getLabel(item) }}</span>
                    <span v-if="key < values.length - 1">, </span>
                </span>
            </template>
            <span v-else class="text-gray-400">—</span>
        </template>
    </PanelItem>
</template>

<script>
export default {
    name: "BelongsToManyDetailField",
    props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
    computed: {
        values() {
            if (!this.field.value) return [];
            if (Array.isArray(this.field.value)) return this.field.value;
            return [];
        },
        hasValues() {
            return this.values.length > 0;
        }
    },
    methods: {
        getLabel(item) {
            if (!item) return '';
            const labelKey = this.field.optionsLabel || 'name';
            return item[labelKey] || item.label || item.name || item.id || '';
        }
    }
};
</script>
