const { compose } = wp.compose;

const {
	withSelect,
	withDispatch,
} = wp.data;

const {
	TextControl,
	ToggleControl,
	SelectControl,
	withNotices,
} = wp.components;

const {
	useEffect,
} = wp.element;

const {
	renderGateway,
} = JetFBActions;

const {
	withSelectGateways,
	withDispatchGateways,
} = JetFBHooks;

function StripeMain( {
	setGatewayRequest,
	gatewaySpecific,
	setGatewaySpecific,
	gatewayScenario,
	setGatewayScenario,
	getSpecificOrGlobal,
	additionalSourceGateway,
	specificGatewayLabel,
	noticeOperations,
	noticeUI,
} ) {

	const {
		id: scenario = 'PAY_NOW',
	} = gatewayScenario;

	useEffect( () => {
		setGatewayRequest( { id: scenario } );
	}, [ scenario ] );

	useEffect( () => {
		setGatewayRequest( { id: scenario } );
	}, [] );

	return <>
		{ noticeUI }
		<ToggleControl
			key={ 'use_global' }
			label={ specificGatewayLabel( 'use_global' ) }
			checked={ gatewaySpecific.use_global }
			onChange={ use_global => setGatewaySpecific( { use_global } ) }
		/>
		<TextControl
			label={ specificGatewayLabel( 'public' ) }
			key='stripe_client_id_setting'
			value={ getSpecificOrGlobal( 'public' ) }
			onChange={ value => setGatewaySpecific( { public: value } ) }
			disabled={ gatewaySpecific.use_global }
		/>
		<TextControl
			label={ specificGatewayLabel( 'secret' ) }
			key='stripe_secret_setting'
			value={ getSpecificOrGlobal( 'secret' ) }
			onChange={ secret => setGatewaySpecific( { secret } ) }
			disabled={ gatewaySpecific.use_global }
		/>
		<SelectControl
			labelPosition='side'
			label={ specificGatewayLabel( 'gateway_type' ) }
			value={ scenario }
			onChange={ id => {
				setGatewayScenario( { id } );
			} }
			options={ additionalSourceGateway.scenarios }
		/>
		{ renderGateway( 'stripe', { noticeOperations }, scenario ) }
	</>;
}

export default compose(
	withSelect( withSelectGateways ),
	withDispatch( withDispatchGateways ),
	withNotices,
)( StripeMain );