using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace ProductCrawler.Delicacies
{
    class Delicacy : ICrawler
    {
        public string URL = "https://api.idelivery.com.tw/platform/storeinfo?id=";

        public List<string> DataList = new List<string>();

        private int _start = 0;
        private int _count = 0;

        public Delicacy(int start, int end)
        {
            _count = end - start + 1;
            _start = start;
        }

        public async Task Get()
        {
            HttpClient httpClient = new HttpClient();

            httpClient.DefaultRequestHeaders.TryAddWithoutValidation("Authorization", "NzJhMDZiMzE0NWU0NDlkMGY0ZDMzYTJiMTE5OTYzMGQ0YmU1M2M1ZA==");

            for (var id = 0; id < _count; id++)
            {
                string url = this.URL + (_start + id + 1).ToString();

                var responseMessage = await httpClient.GetAsync(url);

                if (responseMessage.IsSuccessStatusCode)
                {
                    var responseContent = await responseMessage.Content.ReadAsStringAsync();

                    var jsonObject = Newtonsoft.Json.Linq.JObject.Parse(responseContent);

                    if (jsonObject.ContainsKey("data"))
                    {
                        // Console.WriteLine(jsonObject["data"]["company_name"].ToString() + ':' + (id + 1));
                        string data = jsonObject["data"].ToString();
                        DataList.Add(data);
                    }
                    else
                    {
                        Console.WriteLine("No Data.");
                    }
                }
            }
        }

        public void GenerateFile()
        {
            string combinedJson = "[" + string.Join(",", DataList) + "]";

            File.WriteAllText("Product.json", combinedJson);
        }
    }
}
