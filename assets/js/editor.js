const { registerBlockType } = wp.blocks;
const { SelectControl } = wp.components;
const { useSelect } = wp.data;
const { __ } = wp.i18n;

registerBlockType('up-immo/bien-meta', {
    title: 'Bien Immobilier - Métadonnée',
    icon: 'admin-home',
    category: 'widgets',
    attributes: {
        field: {
            type: 'string',
            default: 'prix'
        }
    },
    edit: function(props) {
        const { attributes, setAttributes } = props;
        
        const options = [
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
            return select('core/editor').getEditedPostAttribute('meta');
        }, []);

        const value = postMeta ? postMeta[attributes.field] : '';

        return (
            <div className="up-immo-meta-block">
                <SelectControl
                    label="Choisir le champ"
                    value={attributes.field}
                    options={options}
                    onChange={(field) => setAttributes({ field })}
                />
                <div className="up-immo-meta-preview">
                    {value || 'Aucune valeur'}
                </div>
            </div>
        );
    },
    save: function() {
        return null; // Utilise le rendu côté serveur
    }
}); 