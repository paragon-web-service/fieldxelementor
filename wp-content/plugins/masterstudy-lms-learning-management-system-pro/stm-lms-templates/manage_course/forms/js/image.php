<?php ob_start(); ?>
	<script>
		Vue.component('stm-image', {
			props: ['image_id', 'label'],
			data: function () {
				return {
					image: {
						id: '',
						url: '',
						file: '',
						message: '',
					},
					mediaLibrary: false,
					desc : "<?php esc_html_e( 'Upload course Image', 'masterstudy-lms-learning-management-system-pro' ); ?>",
					loading: false,
				}
			},
			components: ['media-library'],
			mounted: function () {
				var _this = this;
				if(typeof _this.image_id !== 'undefined') {
					_this.loading = true;
					_this.$http.get(stm_lms_ajaxurl + '?action=stm_lms_pro_get_image_data&nonce=' + stm_lms_pro_nonces['stm_lms_pro_get_image_data'] + '&image_id=' + _this.image_id).then(function (res) {
						_this.$set(_this.image, 'id', _this.image_id);
						_this.$set(_this.image, 'url', res.body);
						_this.loading = false;
					});
				}

				if(typeof _this.label !== 'undefined') _this.desc = _this.label;
			},
			template: '<?php /*phpcs:ignore */ echo preg_replace( "/\r|\n/", '', addslashes( STM_LMS_Templates::load_lms_template( 'manage_course/forms/html/image' ) ) ); ?>',
			methods: {
				getRandomInt(n) {
					return Math.floor(Math.random() * n);
				},

				closeModal() {
					this.mediaLibrary = false;
					let _this = this;
				},

				showMediaLibrary() {
					document.body.classList.add('media_open');
					this.mediaLibrary = !this.mediaLibrary
				},

				checkedImage(file) {
					let url  = file.url + "?r=" + this.getRandomInt(99);
					this.$set(this.image, 'file', '');
					this.$set(this.image, 'id', file.id);
					this.$set(this.image, 'url', url);
				},
				loadImage: function () {
					var _this = this;
					var fileToUpload = _this.$refs['image-file'].files[0];

					if (fileToUpload) {

						var formData = new FormData();
						_this.loading = true;

						formData.append('image', fileToUpload);
						formData.append('action', 'stm_lms_pro_upload_image');
						formData.append('nonce', stm_lms_pro_nonces['stm_lms_pro_upload_image']);

						_this.$set(_this.image, 'message', '');
						_this.$http.post(stm_lms_ajaxurl, formData).then(function (res) {
							_this.loading = false;
							if (res['body']['error'] === 'false') {
								_this.$set(_this.image, 'file', '');
								_this.$set(_this.image, 'id', res['body']['id']);
								_this.$set(_this.image, 'url', res['body']['url']);
							} else {
								_this.$set(_this.image, 'file', '');
								_this.$set(_this.image, 'message', res['body']['message']);
							}
						});
					} else {
						_this.cleanImage();
					}
				},
				cleanImage() {
					var _this = this;
					_this.$set(_this.image, 'id', '');
					_this.$set(_this.image, 'url', '');
				}
			},
			watch: {
				image: {
					handler: function () {
						var _this = this;
						this.$emit('image-changed', _this.image.id);
						this.$emit('image-url', _this.image.url);
					},
					deep: true
				},
			}
		});

	</script>

<?php wp_add_inline_script( 'stm-lms-manage_course', str_replace( array( '<script>', '</script>' ), '', ob_get_clean() ) ); ?>
