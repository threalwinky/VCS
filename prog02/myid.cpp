#include <iostream>
#include <string>
#include <vector>
#include <sstream>

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
int main() {
    string target_user;
    cout << "Nhap username: ";
    cin >> target_user;

    if (freopen("/etc/passwd", "r", stdin) == NULL) {
        perror("Khong the mo /etc/passwd");
        return 1;
    }

    string line;
    bool found = false;
    string uid, gid, home;

    while (cin >> line) {
        vector<string> fields = split(line, ':');
        if (fields.size() >= 6 && fields[0] == target_user) {
            uid = fields[2];
            gid = fields[3];
            home = fields[5];
            found = true;
            break;
        }
    }

    if (!found) {
        cout << "Khong tim thay user tuong ung." << "\n";
        return 0;
    }

    if (freopen("/etc/group", "r", stdin) == NULL) {
        perror("Khong the mo /etc/group");
        return 0;
    }

    vector<string> user_groups;
    while (cin >> line) {
        vector<string> fields = split(line, ':');
        if (fields.size() >= 3) {
            string group_name = fields[0];
            string group_id = fields[2];
            
            if (group_id == gid) {
                user_groups.push_back(group_name);
            } 
            else if (fields.size() == 4 && fields[3].find(target_user) != string::npos) {
                user_groups.push_back(group_name);
            }
        }
    }

    cout << "--- Thong tin user " << target_user << " ---" << "\n";
    cout << "UID: " << uid << "\n";
    cout << "Username: " << target_user << "\n";
    cout << "Home: " << home << "\n";
    cout << "Groups: ";
    for (int i = 0; i < user_groups.size(); ++i) {
        cout << user_groups[i] << (i == user_groups.size() - 1 ? "" : ", ");
    }
    cout << "\n";
}