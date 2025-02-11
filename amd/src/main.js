define([
    'jquery',
    'core/ajax',
    'core/templates',
    'core/str',
    'qrcode',
], function($, Ajax, Templates, str, QRCode) {
    return {
        init: function(courseName, courseUrl, extendedHTML, ltiUrl, ltiCode, hasLti) {
            this.courseName = courseName;
            this.extendedHTML = extendedHTML;
            this.ltiUrl = ltiUrl;
            this.ltiCode = ltiCode;
            this.hasLti = hasLti;
            this.courseUrl = courseUrl;
            this.encodedUrl = encodeURIComponent(courseUrl);

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', this.moveMenuItem.bind(this));
            } else {
                this.moveMenuItem();
            }
        },
        moveMenuItem: function() {
            let shareItemLink = document.querySelector('li[data-key="sharecourse"] a');
            if (! shareItemLink) {
                return;
            }

            // Clone the share link.
            let shareItemClone = shareItemLink.cloneNode(true);
            // Remove role and tabindex attributes
            shareItemClone.removeAttribute('role');
            shareItemClone.removeAttribute('tabindex');
            // Modify the cloned link to be an info button with the fa-share icon.
            shareItemClone.classList.remove('dropdown-item');
            shareItemClone.classList.add('btn', 'btn-sm', 'btn-info', 'd-flex', 'align-items-center', 'ml-3');
            shareItemClone.style.height = 'fit-content';
            shareItemClone.innerHTML = '<i class="fa fa-share mr-2"></i> Share';

            // Find the course title element and insert the cloned link next to it.
            let courseTitle = document.querySelector('.page-header-headings');
            if (courseTitle) {
                courseTitle.parentNode.insertBefore(shareItemClone, courseTitle.nextSibling);
            }

            // Prevent default action and show modal.
            shareItemClone.addEventListener('click', (event) => {
                event.preventDefault();
                this.showModal();
            });
            // Prevent default action and show modal.
            shareItemLink.addEventListener('click', (event) => {
                event.preventDefault();
                this.showModal();
            });
        },
        showModal: async function() {
            // Check if the modal already exists
            let existingModal = document.getElementById('shareModal');
            if (existingModal) {
                $('#shareModal').modal('show');
                return;
            }

            let emailbody = await str.get_string('share_email_body', 'local_sharecourse', {courseurl: this.courseUrl, coursename: this.courseName});

            // Prepare the context data for the template
            const context = {
                courseurl: this.courseUrl,
                encodedurl: this.encodedUrl,
                extendedhtml: this.extendedHTML,
                ltiurl: this.ltiUrl,
                lticode: this.ltiCode,
                haslti: this.hasLti,
                emailbody: encodeURIComponent(emailbody),
            };

            Templates.render('local_sharecourse/modal', context).done(function(html, js) {
                // Append the modal to the body
                document.body.insertAdjacentHTML('beforeend', html);
                Templates.runTemplateJS(js);
                $('#shareModal').modal('show');

                // Generate QR code
                let qrcodeContainer = document.getElementById('qrcode');
                new QRCode(qrcodeContainer, {
                    text: this.courseUrl,
                    width: 256,
                    height: 256,
                });

                // Handle event when copied button are clicked.
                this.addCopyEventListener('copy-courseurl-button', this.courseUrl);
                this.addCopyEventListener('copy-ltiurl-button', this.ltiUrl);
                this.addCopyEventListener('copy-lticode-button', this.ltiCode);

                // Trigger event when modal is loaded.
                const event = new CustomEvent("shareModalLoaded", { bubbles: true });
                document.dispatchEvent(event);
            }.bind(this)).fail(function(ex) {
                console.error('Failed to render template', ex);
            });
        },
        addCopyEventListener: function(buttonId, textToCopy) {
            button = document.getElementById(buttonId);

            if (!button) {
                return;
            }

            button.addEventListener('click', function() {
                let copyButton = document.getElementById(buttonId);

                navigator.clipboard.writeText(textToCopy).then(async function() {
                    // Change tooltip text when copied.
                    let copiedlabel = await str.get_string('copied_clipboard', 'local_sharecourse');
                    copyButton.setAttribute('data-original-title', copiedlabel);
                    $(copyButton).tooltip('show');
                }).catch(function(error) {
                    console.error('Could not copy text: ', error);
                });

                // Reset tooltip title when the mouse leaves the button.
                copyButton.addEventListener('mouseleave', async function() {
                    let copylabel = await str.get_string('copy_clipboard', 'local_sharecourse');
                    copyButton.setAttribute('data-original-title', copylabel);
                    $(copyButton).tooltip('hide');
                }, {once: true});
            });
        }
    };
});
