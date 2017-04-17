#include "Bott.h"
#include "Logger.h"
#include "chaclient.h"
#include "rapidjson/writer.h"
#include "rapidjson/stringbuffer.h"

extern string tmpnam();

Bott::Bott() {
    //ctor
}

Bott::~Bott() {
    //dtor
}

Bott::Bott(string filename) {
    Document document;
    document.Parse(loadAllFromFile(filename).c_str());
    if (document.HasMember("type")) {
        type = document["type"].GetInt();
    }
    if (document.HasMember("runid")) {
        runid = document["runid"].GetInt();
    }
    if (document.HasMember("source")) {
        src = document["source"].GetString();
    }
    if (document.HasMember("compileInfo")) {
        ce_info = document["compileInfo"].GetString();
    }
    if (document.HasMember("language")) {
        language = document["language"].GetInt();
    }
    if (document.HasMember("pid")) {
        pid = document["pid"].GetInt();
    }
    if (document.HasMember("testcases")) {
        number_of_testcases = document["testcases"].GetInt();
    }
    if (document.HasMember("timeLimit")) {
        time_limit = document["timeLimit"].GetInt();
    }
    if (document.HasMember("caseLimit")) {
        case_limit = document["caseLimit"].GetInt();
        if (case_limit == 0) {
            case_limit = time_limit;
        }
    }
    if (document.HasMember("memoryLimit")) {
        memory_limit = document["memoryLimit"].GetInt();
    }
    if (document.HasMember("spjStatus")) {
        spj = document["spjStatus"].GetInt();
    }
    if (document.HasMember("vname")) {
        vname = document["vname"].GetString();
    }
    if (document.HasMember("vid")) {
        vid = document["vid"].GetString();
    }
    if (document.HasMember("memoryUsed")) {
        memory_used = document["memoryUsed"].GetInt();
    }
    if (document.HasMember("timeUsed")) {
        time_used = document["timeUsed"].GetInt();
    }
    if (document.HasMember("result")) {
        result = document["result"].GetString();
    }
    if (document.HasMember("challenge")) {
        if (document["challenge"].HasMember("id")) {
            cha_id = document["challenge"]["id"].GetInt();
        }
        if (document["challenge"].HasMember("dataType")) {
            data_type = document["challenge"]["dataType"].GetInt();
        }
        if (document["challenge"].HasMember("dataLanguage")) {
            data_lang = document["challenge"]["dataLanguage"].GetInt();
        }
        if (document["challenge"].HasMember("dataDetail")) {
            data_detail = document["challenge"]["dataDetail"].GetString();
        }
        if (document["challenge"].HasMember("detail")) {
            cha_detail = document["challenge"]["detail"].GetString();
        }
        if (document["challenge"].HasMember("result")) {
            cha_result = document["challenge"]["result"].GetString();
        }
    }
}

void Bott::addIntValue(Document &document, const char *name, int v) {
    Value value(v);
    document.AddMember(StringRef(name), value, document.GetAllocator());
}

void Bott::addStringValue(Document &document, const char *name,
                          const char *v) {
    Value value(StringRef(v));
    document.AddMember(StringRef(name), value, document.GetAllocator());
}

void Bott::addIntValueToRef(
        Document &document, Value &ref, const char *name, int v) {
    Value value(v);
    document.AddMember(StringRef(name), value, document.GetAllocator());
}

void Bott::addStringValueToRef(Document &document, Value &ref,
                               const char *name, const char *v) {
    Value value(StringRef(v));
    document.AddMember(StringRef(name), value, document.GetAllocator());
}

void Bott::toFile() {
    Document document;
    document.SetObject();
    addIntValue(document, "type", type);
    memory_used /= 1024;

    if (type == CHALLENGE_REPORT) {
        Value challenge;
        challenge.SetObject();
        addIntValueToRef(document, challenge, "id", cha_id);
        addStringValueToRef(document, challenge, "result", cha_result.c_str());
        addStringValueToRef(document, challenge, "detail", cha_detail.c_str());
        document.AddMember("challenge", challenge, document.GetAllocator());
    }
    if (type == RESULT_REPORT) {
        addIntValue(document, "runid", runid);
        addIntValue(document, "memoryUsed", memory_used);
        addIntValue(document, "timeUsed", time_used);
        addStringValue(document, "result", result.c_str());
        addStringValue(document, "compileInfo", ce_info.c_str());
    }
    FILE *fp = fopen(out_filename.c_str(), "w");
    StringBuffer buffer;
    Writer <StringBuffer> writer(buffer);
    document.Accept(writer);
    fprintf(fp, "%s", buffer.GetString());
    fclose(fp);
}
