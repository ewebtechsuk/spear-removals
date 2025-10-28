import * as tab from './stripe-tab';

const {
		  addFilter,
	  } = wp.hooks;

addFilter( 'jet.fb.register.gateways', 'jet-form-builder', tabs => {
	tabs.push( tab );

	return tabs;
} );
