"use strict";

document.querySelectorAll('.input-file').forEach(inputFile => {
    inputFile.querySelector('.input-file__input').addEventListener('change', e => {
        const filepath = inputFile.querySelector('.input-file__input').value;

        let splittedPath;
        if(filepath.indexOf("/") != -1)
            splittedPath = filepath.split("/");
        else
            splittedPath = filepath.split("\\");

        if(splittedPath[splittedPath.length-1].split(".")[1] != "csv")
        {
            inputFile.querySelector('.input-file__input').value = "";
            return;
        }

        document.querySelector(".input-file__file-name").innerHTML = splittedPath[splittedPath.length-1];
    });

    inputFile.querySelector('.input-file__btn').addEventListener('click', e => {
        inputFile.querySelector('.input-file__input').click();
    });
});