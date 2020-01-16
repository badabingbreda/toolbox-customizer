var addRuleTypeCategory = BBLogic.api.addRuleTypeCategory,
    addRuleType = BBLogic.api.addRuleType,
    getFormPreset = BBLogic.api.getFormPreset,
    __ = BBLogic.i18n.__;


addRuleTypeCategory('tbcustomizer', {
    label: 'Toolbox Customizer'
});


addRuleType('tbcustomizer/theme_mod', {
    label: 'Get Theme Mod',
    category: 'tbcustomizer',
	form: function form(_ref) {
		var rule = _ref.rule;
		var theme_mod = rule.theme_mod;
		var operator = rule.operator;

		return {
            theme_mod: {
                type: 'text',
                placeholder: 'enter theme mod name',
            },
			operator: {
				type: 'operator',
				operators: [ 'equals', 'does_not_equal', 'is_set', 'is_not_set' ],
			},
            compare: {
                type: 'text',
                placeholder: '',
				visible: ( [ 'equals' , 'does_not_equal' ].indexOf( operator ) >= 0 ) ,
            },

		};
	}
});
