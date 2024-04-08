using ProductWebCrawler.Delicacies;
using System;
using System.Collections.Generic;
using System.IO;
using System.Net.Http;
using System.Threading.Tasks;

namespace ProductWebCrawler
{
    class Program
    {
        static async Task Main(string[] args)
        {
            Console.WriteLine("請輸入產品頁數:");

            int page = int.Parse(Console.ReadLine());

            Console.WriteLine("您輸入的是：" + page);

            var delicacy = new Delicacy(page);

            await delicacy.Get();

            delicacy.GenerateFile();
        }
    }
}
