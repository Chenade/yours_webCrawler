using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace ProductWebCrawler.Delicacies
{
    class Delicacy : ICrawler
    {
        public string URL = "https://api.idelivery.com.tw";

        public List<string> DataList = new List<string>();

        private int _page = 0;

        private int _limit = 20;

        private HttpClient _httpClient;

        public Delicacy(int page)
        {
            _page = page;
            _httpClient = new HttpClient();
        }

        public async Task Get()
        {
            _httpClient.DefaultRequestHeaders.TryAddWithoutValidation("Authorization", "NzJhMDZiMzE0NWU0NDlkMGY0ZDMzYTJiMTE5OTYzMGQ0YmU1M2M1ZA==");

            string url = "";

            for (var page = 0; page < _page; page++)
            {
                url = this.URL + "/platform/storelist?page=" + (page + 1).ToString() + "&limit=" + _limit;

                Console.WriteLine(url);

                var responseStores = await _httpClient.GetAsync(url);

                if (responseStores.IsSuccessStatusCode)
                {
                    var responseStoresContent = await responseStores.Content.ReadAsStringAsync();
                    var responseStoresJsonObject = Newtonsoft.Json.Linq.JObject.Parse(responseStoresContent);

                    if (responseStoresJsonObject.ContainsKey("data"))
                    {
                        var stores = responseStoresJsonObject["data"]["stores"]["popular"];

                        foreach(var store in stores)
                        {
                            var storeId = store["id"].ToString();

                            var storeInfo = await GetStoreInfo(storeId);

                            if (storeInfo.ContainsKey("data"))
                            {
                                Console.WriteLine(storeInfo["data"]["company_name"].ToString());

                                var companyId = storeInfo["data"]["company_id"].ToString();
                                var company = await GetCompany(companyId, storeId);

                                if(company.ContainsKey("response"))
                                {
                                    storeInfo["data"]["company"] = company["response"];
                                    string data = storeInfo["data"].ToString();
                                    DataList.Add(data);
                                }

                            }
                        }
                    }
                    else
                    {
                        Console.WriteLine("No Data.");
                    }
                }
            }
        }

        public async Task<JObject> GetStoreInfo(string storeId)
        {
            var url = this.URL + "/platform/storeinfo?id=" + storeId;

            var responseStoreInfo = await _httpClient.GetAsync(url);

            if (responseStoreInfo.IsSuccessStatusCode)
            {
                var responseStoreInfoContent = await responseStoreInfo.Content.ReadAsStringAsync();
                return Newtonsoft.Json.Linq.JObject.Parse(responseStoreInfoContent);
            }

            return new JObject();
        }

        public async Task<JObject> GetCompany(string companyId, string storeId)
        {
            var url = this.URL + "/company/" + companyId + "/config?lang=en&store_id=" + storeId + "&from=IMENU";

            var responseCompany = await _httpClient.GetAsync(url);

            if (responseCompany.IsSuccessStatusCode)
            {
                var responseCompanyContent = await responseCompany.Content.ReadAsStringAsync();

                return Newtonsoft.Json.Linq.JObject.Parse(responseCompanyContent);
            }

            return new JObject();
        }

        public void GenerateFile()
        {
            string combinedJson = "[" + string.Join(",", DataList) + "]";

            File.WriteAllText("Product.json", combinedJson);
        }
    }
}
