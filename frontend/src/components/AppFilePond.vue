<script setup>
    import { ref } from 'vue'

    import vueFilePond from "vue-filepond";
    import "filepond/dist/filepond.min.css";
    import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
    import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
    import FilePondPluginImagePreview from "filepond-plugin-image-preview";
    import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";
    //import { default as ru } from 'filepond/locale/ru-ru.js';

    const FilePond = vueFilePond(
        FilePondPluginFileValidateType,
        FilePondPluginImagePreview,
        FilePondPluginFileValidateSize
    );
        
    //FilePond.setOptions(ru);

    const props = defineProps({
        label: {},
        acceptedFileTypes: {
            default: 'image/jpeg, image/png, image/bmp'
        },
        caption: {
            default: "размер файла не должен превышать 1 МБ<br>(допустимые расширения: jpg, png, bmp)"
        },
        maxFileSize: {
            default: "1MB"
        }
    })

    const pond = ref(null)

    const getFile = function() {
        console.log(pond.value.getFiles()[0])

        if (pond.value.getFiles()[0] && pond.value.getFiles()[0].file instanceof File) {
            return pond.value.getFiles()[0].file
        }

        if (pond.value.getFiles()[0] && pond.value.getFiles()[0].file instanceof Blob) {
            return new File([pond.value.getFiles()[0].file], pond.value.getFiles()[0].filename, {
                type: pond.value.getFiles()[0].fileType,
            });
        }

        return
    }

    const getFiles = function() {
        console.log(pond.value.getFiles())

        const out = []
        const files = pond.value.getFiles() || []

        for (let file of files) {
            if (file.status === 2) {
                if (file.file instanceof File) {
                    out.push(file.file)
                } else if (file.file instanceof Blob) {
                    const blobToFile = new File([file.file], file.filename, {
                        type: file.fileType,
                    });
                    out.push(blobToFile)
                }
            }
        }

        return out
    }

    defineExpose({
        getFile,
        getFiles
    })
</script>

<template>
    <file-pond
        ref="pond"
        :accepted-file-types="acceptedFileTypes"
        :max-file-size="maxFileSize"
        :label-idle='`
        <div><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>cloud-upload-outline</title><path d="M6.5 20Q4.22 20 2.61 18.43 1 16.85 1 14.58 1 12.63 2.17 11.1 3.35 9.57 5.25 9.15 5.88 6.85 7.75 5.43 9.63 4 12 4 14.93 4 16.96 6.04 19 8.07 19 11 20.73 11.2 21.86 12.5 23 13.78 23 15.5 23 17.38 21.69 18.69 20.38 20 18.5 20H13Q12.18 20 11.59 19.41 11 18.83 11 18V12.85L9.4 14.4L8 13L12 9L16 13L14.6 14.4L13 12.85V18H18.5Q19.55 18 20.27 17.27 21 16.55 21 15.5 21 14.45 20.27 13.73 19.55 13 18.5 13H17V11Q17 8.93 15.54 7.46 14.08 6 12 6 9.93 6 8.46 7.46 7 8.93 7 11H6.5Q5.05 11 4.03 12.03 3 13.05 3 14.5 3 15.95 4.03 17 5.05 18 6.5 18H9V20M12 13Z" /></svg></div>
        <p>${label}</p>
        <p>${caption}</p>
        `'
    />
</template>

<style lang="scss">
    .filepond--wrapper {
        border: 2px dashed rgba(30, 33, 57, 0.2);
        border-radius: 6px;
    }

    .filepond--root {
        margin-bottom: 0;
    }

    .filepond--panel-root {
        background-color: #fff;
    }

    .filepond--root .filepond--drop-label {
        min-height: 230px;
        // border-radius: 6px;
        font-size: 14px;
        color: var(--color-text);
        font-family: 'Nunito Sans', sans-serif !important;

        svg {
            width: 44px;
            fill: #6c757d;
        }

        p:first-of-type {
            margin: 0 0 8px;
            font-size: 24px;
        }
    }

    .filepond--grid .filepond--item {
        @media (min-width: 992px) {
            width: calc(50% - 0.5em);
        }
    }
</style>