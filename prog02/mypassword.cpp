#include <iostream>
#include <fstream>
#include <string>
#include <vector>
#include <sstream>
#include <ctime>
#include <cstdlib>
#include <unistd.h>
#include <crypt.h>

using namespace std;

vector<string> split(string s, char del) {
    vector<string> res;
    stringstream ss(s);
    string item;
    while (getline(ss, item, del)) {
        res.push_back(item);
    }
    if (!s.empty() && s.back() == del) {
        res.push_back("");
    }
    return res;
}

string generate_salt() {
    string alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/.";
    srand(time(0));
    string salt = "$6$";
    int salt_length = 16;
    for (int i = 0; i < salt_length; i++) {
        salt += alphabet[rand() % alphabet.length()];
    }
    salt += "$";
    return salt;
}

string get_existing_salt(string hashed_passwd) {
    size_t last = hashed_passwd.find_last_of('$');
    if (last == string::npos) return "";
    return hashed_passwd.substr(0, last + 1);
}

int main() {
    string username;
    cout << "Nhap username: ";
    cin >> username;

    if (freopen("/etc/shadow", "r", stdin) == NULL) {
        perror("Loi mo file");
        return 1;
    }

    vector<string> res;
    string line;
    bool found = false;
    bool success = false;

    while (getline(cin, line)) {
        vector<string> fields = split(line, ':');

        if (!fields.empty() && fields[0] == username) {
            found = true;
            string curhash = fields[1];
            
            FILE* tty = fopen("/dev/tty", "r");
            if (!tty) {
                cerr << "Loi mo terminal" << endl;
                return 1;
            }

            char pass_buf[256];
            cout << "Current password: ";
            if (fgets(pass_buf, sizeof(pass_buf), tty)) {
                string curpass(pass_buf);
                if (!curpass.empty() && curpass.back() == '\n') curpass.pop_back();

                string salt = get_existing_salt(curhash);
                char* testhash = crypt(curpass.c_str(), salt.c_str());

                if (testhash && string(testhash) == curhash) {
                    cout << "Enter new password: ";
                    if (fgets(pass_buf, sizeof(pass_buf), tty)) {
                        string new_passwd(pass_buf);
                        if (!new_passwd.empty() && new_passwd.back() == '\n') new_passwd.pop_back();

                        string newsalt = generate_salt();
                        char* newhash = crypt(new_passwd.c_str(), newsalt.c_str());

                        fields[1] = string(newhash);
                        success = true;

                        string new_line = "";
                        for (size_t i = 0; i < fields.size(); i++) {
                            new_line += fields[i] + (i == fields.size() - 1 ? "" : ":");
                        }
                        res.push_back(new_line);
                    }
                } else {
                    fclose(tty);
                    cerr << "Wrong password!" << endl;
                    return 1;
                }
            }
            fclose(tty);
        } else {
            res.push_back(line);
        }
    }

    if (!found) {
        cerr << "Khong tim thay user." << endl;
        return 1;
    }

    if (success) {
        if (freopen("/etc/shadow", "w", stdout) == NULL) {
            perror("Loi ghi file");
            return 1;
        }

        for (const string& l : res) {
            cout << l << "\n";
        }

        fclose(stdout);
        freopen("/dev/tty", "w", stdout);
        cout << "\n--- Da doi mat khau thanh cong ---\n";
    }

    return 0;
}