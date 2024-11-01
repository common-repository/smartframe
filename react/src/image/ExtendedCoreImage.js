const {Fragment} = wp.element;
const {InspectorControls} = wp.editor;
const {PanelBody, TextControl} = wp.components;
import {Spinner} from '@wordpress/components';

const {CheckboxControl} = wp.components;
import {SelectControl, Button, Modal} from '@wordpress/components';

function geDefaultTheme() {


    if (SmartFrameAvailableThemes.length >= 1) {
        return SmartFrameAvailableThemes[0].value

    }
    return "";
}

class ExtendedCoreImage extends TextControl {
    maxImageWidth = 320;
    maxImageHeight = 120;


    state = {
        imageUrl: "",
        imageDimension:
            {
                naturalWidth: 0,
                naturalHeight: 0,
                refreshingImage: false,
            }
        ,
        inputsValues: [
            {caption: ''},
        ],
        isOpen: false,
        smartFrame: '',
        useSmartFrameBlob: false
    };

    constructor(props) {
        super(props);
        this.extractDataFromAlt();
        this.checkImageIsUploadingAndAddDefaultOptions(this.props);
        this.getCurrentCaption(props.attributes.id);
    }

    checkImageIsUploadingAndAddDefaultOptions = () => {
        if (undefined !== this.props.attributes.url) {
            let myRe = /^blob/g;
            if (myRe.exec(this.props.attributes.url) !== null) {
                this.checkImageDimension2(this.props.attributes.url);
            }
        }
    };

    checkImageIsJpg = () => {
        if (undefined !== this.props.attributes.url) {
            let myRe = /w*.jpg/g;
            if (myRe.exec(this.props.attributes.url) !== null) {
                return true;
            }
        }
        return false;
    };

    extractDataFromAlt = () => {
        let theme = "";

        if (this.props.attributes.alt.smartframe_theme === undefined && this.props.attributes.smartframe_theme === "") {
            theme = geDefaultTheme();
            this.props.setAttributes({
                smartframe_theme: theme,
            });
        }
        if (this.props.attributes.alt.smartframe_use_smartframe !== undefined) {
            this.props.setAttributes({
                smartframe_theme: this.props.attributes.alt.smartframe_theme,
                smartframe_caption: this.props.attributes.alt.smartframe_caption,
                use_smartframe: this.props.attributes.alt.smartframe_use_smartframe ? 'yes' : 'no',
                alt: this.props.attributes.alt.alt
            });
        }
    };

    generateSmartFrame = (value) => {
        {
            this.props.setAttributes({use_smartframe: value ? 'yes' : 'no'});
            if (value === true) {
                $.ajax({
                    url: ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
                    data: {
                        'action': 'loadSmartFrameByPostId',
                        'imageId': this.props.attributes.id
                    },
                    success: function (data) {
                        console.log(data);
                    },
                    error: function (errorThrown) {
                        console.log(errorThrown);
                    }
                });

            }
        }
    };

    checkImageDimension(url) {

        if (url !== this.state.imageUrl || this.state.imageDimension.refreshingImage === false) {
            var img = new Image();
            var that = this;
            that.state.imageDimension.refreshingImage = true;
            img.addEventListener("load", function () {
                setTimeout(function () {
                    that.state.imageDimension.refreshingImage = false;
                }, 1000);
                that.setState({
                    imageUrl: url, imageDimension:
                        {
                            naturalWidth: this.naturalWidth,
                            naturalHeight: this.naturalHeight,
                        }
                })
            });
            img.src = url;
        }
    }

    checkImageDimension2(url) {

        var img = new Image();
        var that = this;
        that.state.imageDimension.refreshingImage = true;
        img.addEventListener("load", function () {


            if (this.naturalWidth > SmartFrameSettings.minWidth
                && this.naturalHeight > SmartFrameSettings.minHeight
                && SmartFrameConvertAllImages && !SmartFrameStorageExceeded) {
                that.props.attributes.use_smartframe = 'yes';
                that.setState({
                    useSmartFrameBlob: true
                })
            }

        });
        img.src = url;

    }

    getCurrentCaption(imageId) {
        var that = this;

        $.ajax({
            url: ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
            data: {
                'action': 'getCaptionForAttachment',
                'imageId': imageId
            },
            success: function (data) {
                if (that.props.attributes.smartframe_caption !== data.smartframeCaption) {
                    that.props.setAttributes({smartframe_caption: data.smartframeCaption});
                }
            },
            error: function (errorThrown) {

            }
        });
    }


    openModal = (event) => {
        var that = this;
        this.setState({isOpen: true});
        $.ajax({
            url: ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
            data: {
                'action': 'loadSmartFrameByPostId',
                'imageId': this.props.attributes.id
            },
            success: function (data) {
                //
                var template = $($.parseHTML(data.template));
                template.attr('theme', that.props.attributes.smartframe_theme);
                template.css('width', that.state.imageDimension.naturalWidth + 'px');
                template.css('height', that.state.imageDimension.naturalHeight + 'px');
                that.setState({smartFrame: template.get(0).outerHTML});
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            }
        });
    };

    sendSaveCaptionCall = (value) => {
        var that = this;

        this.props.setAttributes({smartframe_caption: value});
        $.ajax({
            url: ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
            data: {
                'action': 'saveCaptionForAttachment',
                'imageId': this.props.attributes.id,
                'caption': value,
            },
            success: function (data) {
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            }
        });
    };


    render() {
        this.extractDataFromAlt();
        if (this.props.attributes.width === undefined && this.props.attributes.height === undefined) {
            this.checkImageDimension(this.props.attributes.url)
        } else {
            this.state.imageDimension.naturalWidth = this.props.attributes.width;
            this.state.imageDimension.naturalHeight = this.props.attributes.height;
        }
        const {attributes, setAttributes} = this.props;

        if (SmartFrameCode === 0) {

            return (
                <PanelBody title='SmartFrame Settings'>
                    <p>In this section you can enable or disable SmartFrame and apply your favourite theme or
                        caption. Please note that SmartFrame supports JPEG only. </p>
                    <a href={SmartFrameUrl.settingsPage}>
                        Enable SmartFrame for your website</a>
                </PanelBody>

            );
        }

        let captionAndTheme = '';
        let imageDimmensionAlert = '';
        let checkBoxUseSmartframe = '';
        let storageInfo = '';
        let seePreview = '';

        if (SmartFrameStorageExceeded) {
            storageInfo =
                <p style={{color: 'red'}}>You reached the SmartFrame storage limit.&nbsp;
                    <a target="_blank" href={SmartFrameUrl.upgradePlan}>Upgrade your plan</a>
                </p>;
        } else {
            seePreview = <span> - <a className="smartframe-modal-preview-open" href='#'
                                     onClick={this.openModal}>See preview</a></span>;
        }


        let loc = new URL(attributes.url);
        if (loc.hostname === window.location.hostname) {

            checkBoxUseSmartframe =
                <CheckboxControl
                    label={<span> Use as SmartFrame {seePreview}</span>}
                    checked={attributes.use_smartframe === 'yes'}
                    disabled={SmartFrameStorageExceeded}
                    onChange={this.generateSmartFrame}
                />;
            if (this.maxImageWidth > this.state.imageDimension.naturalWidth || this.maxImageHeight > this.state.imageDimension.naturalHeight) {
                if (!SmartFrameStorageExceeded) {
                    imageDimmensionAlert =
                        <div>
                            <div className="components-base-control__field">
                                <p className="">⚠️ SmartFrame theme and caption are not available when
                                    image
                                    dimensions are smaller than 320x120px.</p>
                            </div>
                        </div>
                    ;
                }
            } else {
                captionAndTheme = <div>
                    <SelectControl
                        label="Smartframe Theme"
                        value={attributes.smartframe_theme}
                        options={SmartFrameAvailableThemes}
                        onChange={value => {
                            setAttributes({smartframe_theme: value});
                        }}
                        disabled={attributes.use_smartframe === 'no' || attributes.use_smartframe === false}
                    />
                    <a className='smartframe--manage-themes-image-edit'
                       href={SmartFrameUrl.manageThemes}
                       target="_blank">
                        Manage themes
                    </a>
                    <TextControl
                        label="Smartframe Caption"
                        value={attributes.smartframe_caption}
                        onChange={this.sendSaveCaptionCall}
                        disabled={attributes.use_smartframe === 'no' || attributes.use_smartframe === false}
                    />
                </div>;
            }
        } else {
            let myRe = /^blob/g;
            if (myRe.exec(this.props.attributes.url) === null) {
                checkBoxUseSmartframe = <div>
                    <div className="components-base-control__field">
                        <p className="">⚠️ SmartFrame is available only for images stored in the Media
                            Library. </p>
                    </div>
                </div>;
            }
        }


        if (!this.checkImageIsJpg()) {
            return (
                <PanelBody title='SmartFrame Settings'>

                    <p>In this section you can enable or disable SmartFrame and apply your favourite theme or
                        caption. Please note that SmartFrame supports JPEG only.</p>
                    {loc.hostname !== window.location.hostname && checkBoxUseSmartframe}
                </PanelBody>
            );
        }


        return (
            <PanelBody title='SmartFrame Settings'>

                <p>In this section you can enable or disable SmartFrame and apply your favourite theme or
                    caption. Please note that SmartFrame supports JPEG only.</p>
                {imageDimmensionAlert}
                {storageInfo}
                {checkBoxUseSmartframe}

                {this.state.isOpen && (
                    <Modal
                        className='smartframe-modal-preview'
                        title="SmartFrame Preview"
                        onRequestClose={() => this.setState({isOpen: false, smartFrame: ''})}>
                        {this.state.smartFrame === '' && (
                            <div className='smartframe-modal-preview-content'>
                                <Spinner className='smartframe-preview-spinner'/>
                            </div>
                        )}
                        <div className='smartframe-modal-preview-smartframe'
                             dangerouslySetInnerHTML={{__html: this.state.smartFrame}}/>
                    </Modal>
                )}

                {captionAndTheme}
            </PanelBody>
        );
    }


}


export default ExtendedCoreImage;
