(function (blocks, editor, components, i18n, element) {
    var el = element.createElement;
    var __ = i18n.__;

    blocks.registerBlockType('afflink/insert-button', {
        title: __('Affiliate Link'),
        icon: 'admin-links',
        category: 'widgets',
        attributes: {
            slug: { type: 'string' }
        },
        edit: function (props) {
            return el('div', {},
                el('input', {
                    type: 'text',
                    placeholder: __('Enter slug...'),
                    value: props.attributes.slug,
                    onChange: function (e) {
                        props.setAttributes({ slug: e.target.value });
                    }
                })
            );
        },
        save: function (props) {
            var slug = props.attributes.slug;
            return el('a', {
                href: '/go/' + slug,
                target: '_blank',
                rel: 'nofollow'
            }, slug);
        }
    });
})(
    window.wp.blocks,
    window.wp.blockEditor || window.wp.editor,
    window.wp.components,
    window.wp.i18n,
    window.wp.element
);
