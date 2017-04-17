/* 
 * File:   DatabaseHandler.cpp
 * Author: 51isoft
 * 
 * Created on 2014年1月12日, 下午11:21
 */

#include "DatabaseHandler.h"

DatabaseHandler::DatabaseHandler() {
    mysql = new MYSQL;
    mysql_init(mysql);
    bool reconnect_flag = true;
    mysql_options(mysql, MYSQL_OPT_RECONNECT, &reconnect_flag);
    mysql_options(mysql, MYSQL_SET_CHARSET_NAME, "utf8");
    if (!mysql_real_connect(mysql,
                            CONFIG->Getdatabase_ip().c_str(),
                            CONFIG->Getdatabase_user().c_str(),
                            CONFIG->Getdatabase_password().c_str(),
                            CONFIG->Getdatabase_table().c_str(),
                            CONFIG->Getdatabase_port(),
                            NULL,
                            0)) {
        throw Exception("Cannot connect to DB");
    }
}

DatabaseHandler::~DatabaseHandler() {
    mysql_close(mysql);
    delete mysql;
}

/**
 * Get all results of a query, formatted in both numeric (using string) and key
 * @param query The SQL to be executed
 * @return      The results
 */
vector<map<string, string> > DatabaseHandler::Getall_results(string query) {
    mysql_ping(mysql);
    mysql_query(mysql, query.c_str());
    MYSQL_RES *res = mysql_use_result(mysql);

    // init field names
    MYSQL_FIELD *fields = mysql_fetch_field(res);
    int num_fields = mysql_num_fields(res);

    // fetch all rows
    MYSQL_ROW row;
    vector<map<string, string> > result;
    while ((row = mysql_fetch_row(res))) {
        map<string, string> tmp;
        tmp.clear();
        for (int i = 0; i < num_fields; ++i) {
            tmp[fields[i].name] = row[i];
            tmp[intToString(i)] = row[i];
        }
        result.push_back(tmp);
    }

    mysql_free_result(res);

    return result;
}

/**
 * Do a DB query
 * @param query SQL query string
 */
void DatabaseHandler::query(string query) {
    mysql_ping(mysql);
    mysql_query(mysql, query.c_str());
}

/**
 * Do mysql_real_escape on the string
 * @param str   Original string
 * @return Escaped string
 */
string DatabaseHandler::escape(string str) {
    char *res = new char[str.length() * 2 + 1];
    mysql_real_escape_string(mysql, res, str.c_str(), str.length());
    str = res;
    delete[] res;
    return str;
}