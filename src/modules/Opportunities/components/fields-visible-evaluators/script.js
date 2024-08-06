app.component("fields-visible-evaluators", {
    template: $TEMPLATES["fields-visible-evaluators"],

    props: {
        entity: {
            type: Entity,
            required: true,
        },
    },

    setup(props, { slots }) {
        const hasSlot = (name) => !!slots[name];
        const messages = useMessages();
        const text = Utils.getTexts("fields-visible-evaluators");

        return { hasSlot, messages, text };
    },

    data() {
        return {
            fields: this.fieldSkeleton(),
            avaliableEvaluationFields: {
                ... this.entity.opportunity.avaliableEvaluationFields
            },
            selectAll: false,
            searchQuery: "",
        };
    },

    mounted() {
        this.getFields();
    },

    computed: {
        filteredFields() {
            const query = this.searchQuery.toLowerCase();
            let fields = this.getFields();
            return fields.filter(field =>
                field.title.toLowerCase().includes(query) || (field.id && field.id.toString().includes(query))
            );
        }
    },

    methods: {
        fieldSkeleton() {
            let _fields = [
                {
                    checked: false,
                    fieldName: "category",
                    title: __("category", "fields-visible-evaluators"),
                },
                {
                    checked: false,
                    fieldName: "projectName",
                    title: __("projectName", "fields-visible-evaluators"),
                },
                {
                    checked: false,
                    fieldName: "agentsSummary",
                    title: __("agentsSummary", "fields-visible-evaluators"),
                },
                {
                    checked: false,
                    fieldName: "spaceSummary",
                    title: __("spaceSummary", "fields-visible-evaluators"),
                },
                ...$MAPAS?.config?.fieldsToEvaluate,
            ];

            let fields = [];
            for (const item of _fields) {
                item.checked = false;
                item.disabled = false;
                fields.push(item);
            }

            return fields;
        },
        getFields() {
            let avaliableFields = this.entity.opportunity.avaliableEvaluationFields;

            _fields = Object.values(this.fields).map((item, index) => {
                let field = { ...this.fields[index] }

                field.checked = avaliableFields[item.fieldName] == "true" ? true : false;

                if (avaliableFields["category"] && item.categories?.length > 0) {
                    field.disabled = (avaliableFields["category"] == "true" ? false : true);
                    field.titleDisabled = this.text("activateCategory", "fields-visible-evaluators");
                }

                if (item.conditional) {
                    let condidionalField = this.fields.filter(_item => _item.fieldName == item.conditionalField)
                    field.disabled = (avaliableFields[item.conditionalField] == "true" ? false : true);
                    field.titleDisabled = `${this.text('activateField')} '#${condidionalField[0].id}'`
                }

                this.fields[index] = field;

                if (!field.checked) {
                    this.fields.forEach((_item, pos) => {
                        if (_item.conditionalField == field.fieldName) {
                            this.avaliableEvaluationFields[_item.fieldName] = false;
                            this.entity.opportunity.avaliableEvaluationFields[_item.fieldName] = "false"
                        }

                        if (field.fieldName === "category" && _item.categories?.length > 0) {
                            this.avaliableEvaluationFields[_item.fieldName] = false;
                            this.entity.opportunity.avaliableEvaluationFields[_item.fieldName] = "false"
                        }
                    });
                }

            });
        },
        toggleSelectAll() {
            this.fields.forEach((field) => {
                if (this.selectAll) {
                    if (!field.checked) {
                        field.checked = true;
                        this.avaliableEvaluationFields[field.fieldName] = "true";
                    }
                } else {
                    if (field.checked) {
                        field.checked = false;
                        this.avaliableEvaluationFields[field.fieldName] = "false";
                    }
                }
            });

            this.save();
        },

        toggleSelect(fieldName) {
            this.entity.opportunity.avaliableEvaluationFields[fieldName] = this.avaliableEvaluationFields[fieldName] ? "true" : "false";
            this.save();
            this.getFields();
        },
        async save() {
            await this.entity.opportunity.save();
        }
    },
});
