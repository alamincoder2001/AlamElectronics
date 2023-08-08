<div id="collectionInvoice">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<Invoice v-bind:collection_id="collectionId"></Invoice>
			<!-- hello -->
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/components/installmentCollectionInvoice.js"></script>
<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>
<script>
	new Vue({
		el: '#collectionInvoice',
		components: {
			Invoice
		},
		data(){
			return {
				collectionId: parseInt('<?php echo $collectionId;?>')
			}
		}
	})
</script>

