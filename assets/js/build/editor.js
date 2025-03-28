(function(wp) {
    const { registerBlockType } = wp.blocks;
    const { SelectControl } = wp.components;
    const { useSelect } = wp.data;
    const { __ } = wp.i18n;
    const { useBlockProps, InspectorControls } = wp.blockEditor;

    registerBlockType('up-immo/bien-meta', {
        title: 'Bien Immobilier - Métadonnée',
        icon: 'admin-home',
        category: 'widgets',
        supports: {
            align: ['wide', 'full'],
            color: {
                text: true,
                background: true,
                gradients: true
            },
            typography: {
                fontSize: true,
                lineHeight: true,
                __experimentalFontFamily: true,
                __experimentalFontWeight: true,
                __experimentalFontStyle: true,
                __experimentalTextTransform: true,
                __experimentalLetterSpacing: true
            },
            spacing: {
                padding: true,
                margin: true
            }
        },
        attributes: {
            field: {
                type: 'string',
                default: 'prix'
            },
            align: {
                type: 'string'
            }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();
            
            const options = [
                { label: 'Titre', value: 'titre' },
                { label: 'Description', value: 'description' },
                { label: 'Prix', value: 'prix' },
                { label: 'Surface', value: 'surface' },
                { label: 'Pièces', value: 'pieces' },
                { label: 'Chambres', value: 'chambres' },
                { label: 'Code Postal', value: 'code_postal' },
                { label: 'Ville', value: 'ville' },
                { label: 'Référence', value: 'reference' },
                { label: 'DPE', value: 'dpe' }
            ];

            const postMeta = useSelect((select) => {
                const meta = select('core/editor').getEditedPostAttribute('meta');
                const title = select('core/editor').getEditedPostAttribute('title');
                return {
                    ...meta,
                    titre: title
                };
            }, []);

            const value = postMeta ? postMeta[attributes.field] : '';

            return [
                wp.element.createElement(
                    InspectorControls,
                    { key: 'inspector' },
                    wp.element.createElement(
                        'div',
                        { className: 'components-panel__body is-opened' },
                        wp.element.createElement(SelectControl, {
                            label: 'Choisir le champ à afficher',
                            value: attributes.field,
                            options: options,
                            onChange: (field) => setAttributes({ field })
                        })
                    )
                ),
                wp.element.createElement(
                    'div',
                    { ...blockProps },
                    value || 'Aucune valeur'
                )
            ];
        },
        save: function() {
            return null;
        }
    });
})(window.wp); 