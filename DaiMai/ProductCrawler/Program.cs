using ProductCrawler.Delicacies;
using System;
using System.Collections.Generic;
using System.IO;
using System.Net.Http;
using System.Threading.Tasks;

namespace ProductCrawler
{
    class Program
    {
        static async Task Main(string[] args)
        {
            var delicacy = new Delicacy(8000);

            await delicacy.Get();

            delicacy.GenerateFile();
        }
    }
}
