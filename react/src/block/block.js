import {addFilter} from '@wordpress/hooks';
import React from 'react';

const {InspectorControls} = wp.editor;
const {PanelBody, TextControl} = wp.components;
const {CheckboxControl} = wp.components;
const ImageBlockName = 'core/image';


// Register/add the new attribute.
const addExtraAttribute = props => {
    if (props.name !== ImageBlockName) {
        return props;
    }
    const attributes = {
        ...props.attributes,

        smartframe_caption: {
            type: "string",
            attribute: "smartframe_caption",
            default: "",
            // selector: "figure",
            // source: "attribute",
        },
        smartframe_theme: {
            type: "string",
            attribute: "smartframe_theme",
            default: "",
            // selector: "figure",
            // source: "attribute",
        },
        use_smartframe: {
            type: "string",
            attribute: "use_smartframe",
            default: "no",
            // selector: "figure",
            // source: "attribute",
        }
    };

    return {...props, attributes};
};

addFilter(
    "blocks.registerBlockType",
    "core/extra-attribute",
    addExtraAttribute
);


const getBlockAttributes = (attr, asd, asd2) => {
    // asd.edit()._store.validated = true;
    return attr;
};

addFilter(
    "blocks.getBlockAttributes",
    "my-plugin/extra-attribute",
    getBlockAttributes
);

// Add extra props. Here we assign an html
// data-attribute with the extra_attribute value.

function addExtraPropsToHtml(props, blockType, attributes) {
    //     if (blockType.name !== ImageBlockName) {
    //     return props;
    // }
    return lodash.assign(props, {
        "smartframe_caption": attributes.smartframe_caption,
        "smartframe_theme": attributes.smartframe_theme,
        "use_smartframe": attributes.use_smartframe,
    });
}

wp.hooks.addFilter(
    'blocks.getSaveContent.extraProps',
    'my-plugin/add-background-color-style',
    addExtraPropsToHtml
);


//Core Gallery Hooks

