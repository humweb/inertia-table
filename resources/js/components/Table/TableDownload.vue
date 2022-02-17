<template>

    <div>
        <button class="btn btn-outline-gray border-gray-300" @click="openDialog" v-tooltip="'Download'">
            <Icon icon="download" class="w-5 h-5"></Icon>
        </button>
        <DialogModal :show="open" @close="open=false">
            <template v-slot:title><div class="text-xl">Export table data</div></template>
            <template v-slot:content>

                <div class="py-3">
                    <label class="font-medium">Filename</label>
                    <input v-model="filename" type="text" class="form-input mt-2" placeholder="Filename.xls">
                    <div class="flex flex-row pt-2">
                    <div class=" text-gray-400 basis-1/2">Accepted formats are .xls and .csv</div>
                    <div v-if="error" class="text-red-500 basis-1/2 text-right">{{ error }}</div>
                        </div>
                </div>

            </template>
            <template v-slot:footer>
                <button class="btn btn-primary" @click="download">Download</button>
            </template>


        </DialogModal>
    </div>
</template>

<script>
import ButtonWithDropdown from './ButtonWithDropdown.vue';
import DialogModal from '@/Jetstream/DialogModal';

export default {

    components: {
        ButtonWithDropdown,
        DialogModal,
    },
    props: {

        onChange: {
            type: Function,
            required: true,
        },
    },

    data() {
        return {
            open: false,
            filename:'',
            error: null
        };
    },

    methods: {
        openDialog() {
            this.filename = '';
            this.open = true;
        },
        download() {
            if (this.filename.match(/\.(xlsx?|csv)$/i) == null) {
                this.error = 'Invalid file format'
            }
            else {
                this.error = null
                this.open = false;
                this.onChange(1, this.filename);
            }
        },

    },

    computed: {},
};
</script>
