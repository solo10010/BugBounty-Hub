# Templates for Missing HTTP Security Headers


Nuclei has this as a single finding, which isn't particularly helpful for the user - because it doesn't flag those headers that are missing. So we need to have our own templates for this.

I am basically breaking up this Nuclei template into separate templates for each header. This is because I want to be able to flag the specific header that is missing.

> [!NOTE]
> This is also covered in missing-headers folder, but Nuclei v3 has different template syntax. This folder is dedicated to the v3 syntax and will be renamed to missing-headers soon.
